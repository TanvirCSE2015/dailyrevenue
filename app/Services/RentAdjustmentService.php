<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\DailyRentLedger;
use App\Models\Payment;
use App\Models\PlotAllocation;
use App\Models\RentAdjustment;
use App\Models\RentAdjustmentBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class RentAdjustmentService
{
    public function __construct(
        protected DailyRentLedgerService $ledgerService
    ) {}



    /**
     * Applicant-এর Ledger Synchronize করে
     * Financial Summary তৈরি করবে।
     */
    public function getApplicantSummary(Applicant|int $applicant): array
    {
        $applicantId = $applicant instanceof Applicant
            ? $applicant->id
            : $applicant;

        $applicant = Applicant::query()
            ->with(['allocations.plot'])
            ->findOrFail($applicantId);

        $allocations = PlotAllocation::query()
            ->where('applicant_id', $applicantId)
            ->orderBy('id')
            ->get();

        if ($allocations->isEmpty()) {
            return [
                'status' => 'no_allocation',
                'message' => 'এই আবেদনকারীর কোনো Plot Allocation নেই।',
            ];
        }

        /*
        * ১. সব Allocation-এর Ledger Synchronization।
        */
        foreach ($allocations as $allocation) {

            if (
                $allocation->status === 'cancelled' &&
                !$allocation->end_date
            ) {
                return [
                    'status' => 'blocked',
                    'message' => "Cancelled Allocation #{$allocation->id}-এর end_date পাওয়া যায়নি।",
                ];
            }

            $syncResult = $this->ledgerService->syncAllocation(
                $allocation
            );

            if (($syncResult['status'] ?? null) !== 'completed') {
                $blockedDate = $syncResult['blocked_date'] ?? null;

                return [
                    'status' => 'blocked',
                    'message' => 'Daily Rent Ledger Synchronization সম্পূর্ণ হয়নি।'
                        . ($blockedDate ? " তারিখ: {$blockedDate}" : ''),
                ];
            }
        }

        /*
        * ২. Allocation ID সংগ্রহ।
        */
        $allocationIds = $allocations->pluck('id');

        /*
        * ৩. প্রক্রিয়াধীন Payment পরীক্ষা।
        */
        $pendingPaymentExists = Payment::query()
            ->whereIn('allocation_id', $allocationIds)
            ->where('payment_for', 'daily_rent')
            ->whereIn('status', ['initiated', 'pending'])
            ->exists();

        /*
        * ৪. সব Ledger সংগ্রহ।
        */
        $ledgers = DailyRentLedger::query()
            ->whereIn('allocation_id', $allocationIds)
            ->with([
                'adjustments' => fn ($query) =>
                    $query->where('status', 'approved'),
                'paymentAllocations.payment',
            ])
            ->orderBy('rent_date')
            ->orderBy('id')
            ->get();

        /*
        * ৫. প্রকৃত বকেয়া হিসাব।
        */
        $totalDue = 0;
        $outstandingDays = 0;
        $firstDueDate = null;
        $lastDueDate = null;
        $outstandingLedgerIds = [];

        foreach ($ledgers as $ledger) {

            $amounts = $this->calculateLedgerAmounts($ledger);

            if ($amounts['due'] <= 0) {
                continue;
            }

            $totalDue += $amounts['due'];
            $outstandingDays++;

            $date = Carbon::parse($ledger->rent_date)
                ->toDateString();

            $firstDueDate ??= $date;
            $lastDueDate = $date;

            $outstandingLedgerIds[] = $ledger->id;
        }

        /*
        * ৬. Applicant Information এবং Summary Return।
        */
        return [
            'status' => 'completed',

            'applicant' => [
                'id' => $applicant->id,
                'applicant_no' => $applicant->applicant_no,
                'name' => $applicant->name,
                'mobile' => $applicant->mobile,
            ],

            'plots' => $allocations
                ->map(fn ($allocation) =>
                    $allocation->plot?->plot_number
                )
                ->filter()
                ->unique()
                ->values()
                ->all(),

            'allocation_count' => $allocations->count(),

            'total_due' => $this->fromCents($totalDue),

            'outstanding_days' => $outstandingDays,

            'first_due_date' => $firstDueDate,

            'last_due_date' => $lastDueDate,

            'outstanding_ledger_ids' => $outstandingLedgerIds,

            'has_pending_payment' => $pendingPaymentExists,
        ];
    }


    /**
     * Admin সরাসরি Rent Adjustment কার্যকর করবেন।
     *
     * Supported basis:
     * days, dates, amount
     */
    public function applyForAdmin(
        Applicant|int $applicant,
        array $data,
        ?int $adminId = null
    ): RentAdjustmentBatch {

        $applicantId = $applicant instanceof Applicant
            ? $applicant->id
            : $applicant;

        $adminId = $adminId ?? Auth::id();

        if (!$adminId) {
            throw new RuntimeException(
                'Rent Adjustment কার্যকর করার জন্য Admin ID প্রয়োজন।'
            );
        }

        $this->validateInput($data);

        /*
         * ১. আবেদনকারীর সব Allocation সংগ্রহ।
         *
         * Active, Cancelled, Surrendered, Expired
         * কোনো Allocation বাদ দেওয়া হবে না।
         */
        $allocations = PlotAllocation::query()
            ->where('applicant_id', $applicantId)
            ->orderBy('id')
            ->get();

        if ($allocations->isEmpty()) {
            throw new RuntimeException(
                'এই আবেদনকারীর কোনো Plot Allocation পাওয়া যায়নি।'
            );
        }

        /*
         * ২. প্রতিটি Allocation-এর Ledger Synchronization।
         *
         * Active Allocation:
         * বর্তমান তারিখ পর্যন্ত Synchronization।
         *
         * Cancelled Allocation:
         * end_date পর্যন্ত Synchronization।
         *
         * DailyRentLedgerService end_date-এর
         * পরে Ledger তৈরি করবে না।
         */
        foreach ($allocations as $allocation) {

            if (
                $allocation->status === 'cancelled' &&
                !$allocation->end_date
            ) {
                throw new RuntimeException(
                    "Cancelled Allocation #{$allocation->id}-এর "
                    . "end_date পাওয়া যায়নি। "
                    . "প্রথমে বরাদ্দ বাতিলের কার্যকর তারিখ সংরক্ষণ করুন।"
                );
            }

            $syncResult = $this->ledgerService->syncAllocation(
                $allocation
            );

            if ($syncResult['status'] !== 'completed') {

                $blockedDate = $syncResult['blocked_date'] ?? null;

                throw new RuntimeException(
                    'Daily Rent Ledger Synchronization সম্পূর্ণ হয়নি।'
                    . ($blockedDate ? " তারিখ: {$blockedDate}" : '')
                );
            }
        }

        /*
         * ৩. Synchronization সফল হলে Adjustment Transaction।
         */
        return DB::transaction(function () use (
            $applicantId,
            $data,
            $adminId
        ) {

            /*
             * Allocation Lock:
             * Concurrent Modification প্রতিরোধ।
             */
            $allocations = PlotAllocation::query()
                ->where('applicant_id', $applicantId)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $allocationIds = $allocations->pluck('id');

            if ($allocationIds->isEmpty()) {
                throw new RuntimeException(
                    'কোনো Plot Allocation পাওয়া যায়নি।'
                );
            }

            /*
             * ৪. Pending বা Initiated Daily Rent Payment থাকলে
             * Adjustment করা যাবে না।
             */
            $pendingPaymentExists = Payment::query()
                ->whereIn('allocation_id', $allocationIds)
                ->where('payment_for', 'daily_rent')
                ->whereIn('status', ['initiated', 'pending'])
                ->exists();

            if ($pendingPaymentExists) {
                throw new RuntimeException(
                    'আবেদনকারীর একটি Daily Rent Payment প্রক্রিয়াধীন আছে। '
                    . 'Payment-এর চূড়ান্ত ফলাফল না আসা পর্যন্ত '
                    . 'Rent Adjustment করা যাবে না।'
                );
            }

            /*
             * ৫. আবেদনকারীর সব Allocation-এর Ledger সংগ্রহ।
             *
             * Cancelled Allocation-এর পুরোনো বকেয়া Ledger-ও
             * এখানে অন্তর্ভুক্ত হবে।
             */
            $ledgers = DailyRentLedger::query()
                ->whereIn('allocation_id', $allocationIds)
                ->with([
                    'adjustments' => fn ($query) =>
                        $query->where('status', 'approved'),

                    'paymentAllocations.payment',
                ])
                ->orderBy('rent_date')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            /*
             * ৬. প্রতিটি Ledger-এর প্রকৃত বকেয়া হিসাব।
             */
            $outstanding = [];

            foreach ($ledgers as $ledger) {

                $amounts = $this->calculateLedgerAmounts($ledger);

                if ($amounts['due'] > 0) {
                    $outstanding[] = [
                        'ledger' => $ledger,
                        'base' => $amounts['base'],
                        'waived' => $amounts['waived'],
                        'paid' => $amounts['paid'],
                        'due' => $amounts['due'],
                    ];
                }
            }

            if (empty($outstanding)) {
                throw new RuntimeException(
                    'আবেদনকারীর কোনো বকেয়া দৈনিক ভাড়া নেই।'
                );
            }

            /*
             * ৭. Adjustment Basis অনুযায়ী Ledger নির্বাচন।
             */
            $basis = $data['adjustment_basis'];

            $selectedAdjustments = match ($basis) {

                'days' => $this->selectByDays(
                    $outstanding,
                    (int) $data['adjustment_days']
                ),

                'dates' => $this->selectByDates(
                    $outstanding,
                    $data['dates']
                ),

                'amount' => $this->selectByAmount(
                    $outstanding,
                    $this->toCents($data['requested_amount'])
                ),

                default => throw new RuntimeException(
                    'Invalid Adjustment Basis.'
                ),
            };

            if (empty($selectedAdjustments)) {
                throw new RuntimeException(
                    'মওকুফের জন্য কোনো Ledger নির্বাচন করা হয়নি।'
                );
            }

            /*
             * ৮. মোট প্রকৃত মওকুফের পরিমাণ।
             */
            $totalApprovedAmount = array_sum(
                array_column($selectedAdjustments, 'amount')
            );

            /*
             * ৯. Parent Batch তৈরি।
             *
             * বর্তমান Admin Workflow-তে সরাসরি Approved।
             */
            $batch = RentAdjustmentBatch::create([
                'applicant_id' => $applicantId,

                'adjustment_type' => $data['adjustment_type'],

                'adjustment_basis' => $basis,

                'adjustment_days' => $basis === 'days'
                    ? (int) $data['adjustment_days']
                    : null,

                'requested_amount' => $basis === 'amount'
                    ? $this->fromCents(
                        $this->toCents($data['requested_amount'])
                    )
                    : null,

                'approved_amount' => $this->fromCents(
                    $totalApprovedAmount
                ),

                'reason' => $data['reason'],

                'attachment_path' => $data['attachment_path'] ?? null,

                'requested_by' => $adminId,

                'requested_at' => now(),

                'approved_by' => $adminId,

                'approved_at' => now(),

                'status' => 'approved',

                'approval_remarks' => $data['approval_remarks'] ?? null,
            ]);

            /*
             * ১০. Child Adjustment তৈরি।
             */
            foreach ($selectedAdjustments as $item) {

                $ledger = $item['ledger'];

                $waiverAmount = $item['amount'];

                /*
                 * সম্পূর্ণ অবশিষ্ট বকেয়া মওকুফ হলে full।
                 * অন্যথায় partial।
                 */
                $adjustmentMode = $waiverAmount >= $item['due']
                    ? 'full'
                    : 'partial';

                RentAdjustment::create([
                    'batch_id' => $batch->id,

                    'daily_rent_ledger_id' => $ledger->id,

                    'adjustment_mode' => $adjustmentMode,

                    'approved_amount' => $this->fromCents(
                        $waiverAmount
                    ),

                    'status' => 'approved',
                ]);

                /*
                 * ১১. Ledger-এর আর্থিক তথ্য আপডেট।
                 *
                 * Partial waiver হলে status partial হবে না।
                 * Status নির্ভর করবে Payment-এর উপর।
                 */
                $newTotalWaived = $item['waived'] + $waiverAmount;

                $newNetAmount = max(
                    0,
                    $item['base'] - $newTotalWaived
                );

                if ($newNetAmount === 0) {
                    $ledgerStatus = 'waived';
                } elseif ($item['paid'] >= $newNetAmount) {
                    $ledgerStatus = 'paid';
                } elseif ($item['paid'] > 0) {
                    $ledgerStatus = 'partial';
                } else {
                    $ledgerStatus = 'pending';
                }

                $ledger->update([
                    'adjustment_amount' => $this->fromCents(
                        $newTotalWaived
                    ),

                    'net_amount' => $this->fromCents(
                        $newNetAmount
                    ),

                    'status' => $ledgerStatus,
                ]);
            }

            return $batch->load([
                'applicant',
                'requester',
                'approver',
                'adjustments.dailyRentLedger',
            ]);

        }, 3);
    }

    /**
     * Input Validation
     */
    private function validateInput(array $data): void
    {
        $errors = [];

        if (!in_array($data['adjustment_type'] ?? null, [
            'illness',
            'absence',
            'rain',
            'other',
        ], true)) {
            $errors['adjustment_type'] =
                'সঠিক মওকুফের কারণ নির্বাচন করুন।';
        }

        $basis = $data['adjustment_basis'] ?? null;

        if (!in_array($basis, [
            'days',
            'dates',
            'amount',
        ], true)) {
            $errors['adjustment_basis'] =
                'সঠিক মওকুফের পদ্ধতি নির্বাচন করুন।';
        }

        if (
            !isset($data['reason']) ||
            !is_string($data['reason']) ||
            trim($data['reason']) === ''
        ) {
            $errors['reason'] = 'মওকুফের কারণ লিখুন।';
        }

        if ($basis === 'days') {

            if (
                !isset($data['adjustment_days']) ||
                filter_var(
                    $data['adjustment_days'],
                    FILTER_VALIDATE_INT
                ) === false ||
                (int) $data['adjustment_days'] < 1 ||
                (int) $data['adjustment_days'] > 65535
            ) {
                $errors['adjustment_days'] =
                    '১ থেকে ৬৫৫৩৫ দিনের মধ্যে নির্বাচন করুন।';
            }
        }

        if ($basis === 'dates') {

            if (
                empty($data['dates']) ||
                !is_array($data['dates'])
            ) {
                $errors['dates'] =
                    'কমপক্ষে একটি তারিখ নির্বাচন করুন।';
            } else {

                foreach ($data['dates'] as $date) {

                    if (!$this->isValidDate($date)) {
                        $errors['dates'] =
                            'নির্বাচিত তারিখের Format সঠিক নয়।';

                        break;
                    }
                }
            }
        }

        if ($basis === 'amount') {

            $amount = $data['requested_amount'] ?? null;

            if (
                !is_string($amount) &&
                !is_int($amount) &&
                !is_float($amount)
            ) {
                $errors['requested_amount'] =
                    'সঠিক মওকুফের পরিমাণ লিখুন।';

            } elseif (
                !preg_match(
                    '/^\d+(?:\.\d{1,2})?$/',
                    trim((string) $amount)
                ) ||
                $this->toCents($amount) <= 0
            ) {
                $errors['requested_amount'] =
                    'সঠিক মওকুফের পরিমাণ লিখুন। '
                    . 'সর্বোচ্চ দুই ঘর দশমিক ব্যবহার করুন।';
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Strict Y-m-d Date Validation
     */
    private function isValidDate(mixed $date): bool
    {
        if (!is_string($date)) {
            return false;
        }

        $date = trim($date);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        try {
            $parsed = Carbon::createFromFormat('!Y-m-d', $date);

            return $parsed !== false
                && $parsed->format('Y-m-d') === $date;

        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Ledger-এর প্রকৃত আর্থিক হিসাব।
     */
    private function calculateLedgerAmounts(
        DailyRentLedger $ledger
    ): array {

        $base = $this->toCents($ledger->base_amount);

        /*
         * Approved Adjustment থেকে মোট মওকুফ।
         */
        $waived = 0;

        foreach ($ledger->adjustments as $adjustment) {
            $waived += $this->toCents(
                $adjustment->approved_amount
            );
        }

        if ($waived > $base) {
            throw new RuntimeException(
                "Ledger #{$ledger->id}-এর মওকুফের পরিমাণ "
                . 'মূল ভাড়ার চেয়ে বেশি।'
            );
        }

        $expectedNet = $base - $waived;

        /*
         * Ledger-এর stored amount এবং Child Adjustment
         * হিসাবের মধ্যে অমিল থাকলে প্রক্রিয়া বন্ধ।
         */
        $storedAdjustment = $this->toCents(
            $ledger->adjustment_amount
        );

        $storedNet = $this->toCents(
            $ledger->net_amount
        );

        if ($storedAdjustment !== $waived) {
            throw new RuntimeException(
                "Ledger #{$ledger->id}-এর adjustment_amount "
                . 'এবং Approved Adjustment-এর মধ্যে অমিল পাওয়া গেছে।'
            );
        }

        if ($storedNet !== $expectedNet) {
            throw new RuntimeException(
                "Ledger #{$ledger->id}-এর net_amount "
                . 'এবং প্রকৃত হিসাবের মধ্যে অমিল পাওয়া গেছে।'
            );
        }

        /*
         * শুধু Successful এবং Verified Payment গণনা হবে।
         */
        $paid = 0;

        foreach ($ledger->paymentAllocations as $allocation) {

            $payment = $allocation->payment;

            if (
                $payment &&
                $payment->status === 'success' &&
                $payment->verified_at !== null
            ) {
                $paid += $this->toCents(
                    $allocation->amount
                );
            }
        }

        if ($paid > $expectedNet) {
            throw new RuntimeException(
                "Ledger #{$ledger->id}-এ পরিশোধিত অর্থ "
                . 'প্রকৃত ভাড়ার চেয়ে বেশি।'
            );
        }

        $due = $expectedNet - $paid;

        return [
            'base' => $base,
            'waived' => $waived,
            'paid' => $paid,
            'due' => $due,
        ];
    }

    /**
     * Days Based:
     * সবচেয়ে পুরোনো বকেয়া দিন FIFO।
     */
    private function selectByDays(
        array $outstanding,
        int $days
    ): array {

        if ($days > count($outstanding)) {
            throw new RuntimeException(
                "নির্বাচিত {$days} দিনের বিপরীতে "
                . 'পর্যাপ্ত বকেয়া দিন নেই।'
            );
        }

        $selected = array_slice($outstanding, 0, $days);

        return array_map(function ($item) {

            return [
                'ledger' => $item['ledger'],
                'due' => $item['due'],
                'waived' => $item['waived'],
                'base' => $item['base'],
                'paid' => $item['paid'],
                'amount' => $item['due'],
            ];

        }, $selected);
    }

    /**
     * Dates Based:
     * শুধু নির্বাচিত তারিখের বকেয়া।
     */
    private function selectByDates(
        array $outstanding,
        array $dates
    ): array {

        $selectedDates = [];

        foreach (array_unique($dates) as $date) {

            if (!$this->isValidDate($date)) {
                throw new RuntimeException(
                    "Invalid selected date: {$date}"
                );
            }

            $selectedDates[] = trim($date);
        }

        $selected = [];
        $matchedDates = [];

        foreach ($outstanding as $item) {

            $ledgerDate = Carbon::parse(
                $item['ledger']->rent_date
            )->toDateString();

            if (in_array($ledgerDate, $selectedDates, true)) {

                $selected[] = [
                    'ledger' => $item['ledger'],
                    'due' => $item['due'],
                    'waived' => $item['waived'],
                    'base' => $item['base'],
                    'paid' => $item['paid'],
                    'amount' => $item['due'],
                ];

                $matchedDates[] = $ledgerDate;
            }
        }

        /*
         * কোনো নির্বাচিত তারিখে বকেয়া না থাকলে
         * সম্পূর্ণ Adjustment বন্ধ হবে।
         */
        $missingDates = array_diff(
            $selectedDates,
            array_unique($matchedDates)
        );

        if (!empty($missingDates)) {
            throw new RuntimeException(
                'নির্বাচিত তারিখগুলোর মধ্যে বকেয়া পাওয়া যায়নি: '
                . implode(', ', $missingDates)
            );
        }

        if (empty($selected)) {
            throw new RuntimeException(
                'নির্বাচিত তারিখগুলোতে কোনো বকেয়া ভাড়া পাওয়া যায়নি।'
            );
        }

        return $selected;
    }

    /**
     * Amount Based:
     *
     * সকল বকেয়া দিনের মধ্যে সমানভাবে বণ্টন।
     *
     * কোনো দিনের বকেয়া সমান ভাগের চেয়ে কম হলে
     * সেই দিনের সম্পূর্ণ বকেয়া মওকুফ করে অবশিষ্ট
     * টাকা অন্য দিনগুলোতে পুনর্বণ্টন।
     *
     * সব হিসাব পয়সায় (Integer Cents) করা হয়।
     * অবশিষ্ট পয়সা কালানুক্রমে বণ্টন করা হয়।
     */
    private function selectByAmount(
        array $outstanding,
        int $requestedAmount
    ): array {

        $totalDue = array_sum(
            array_column($outstanding, 'due')
        );

        if ($requestedAmount > $totalDue) {
            throw new RuntimeException(
                'নির্ধারিত মওকুফের পরিমাণ মোট বকেয়ার চেয়ে বেশি।'
            );
        }

        if ($requestedAmount <= 0) {
            throw new RuntimeException(
                'মওকুফের পরিমাণ শূন্য বা ঋণাত্মক হতে পারবে না।'
            );
        }

        $remaining = $requestedAmount;

        $capacities = [];

        foreach ($outstanding as $index => $item) {
            $capacities[$index] = $item['due'];
        }

        $allocated = array_fill(
            0,
            count($outstanding),
            0
        );

        while ($remaining > 0) {

            /*
             * যেসব দিনের বকেয়া এখনো অবশিষ্ট আছে।
             */
            $active = [];

            foreach ($capacities as $index => $capacity) {

                if ($capacity > 0) {
                    $active[] = $index;
                }
            }

            if (empty($active)) {
                throw new RuntimeException(
                    'মওকুফের টাকা বণ্টন সম্পূর্ণ করা যায়নি।'
                );
            }

            $count = count($active);

            $share = intdiv($remaining, $count);

            $extraPaisa = $remaining % $count;

            /*
             * কোন দিনের বকেয়া সমান ভাগের চেয়ে কম
             * তা আগে নির্ধারণ।
             */
            $cappedIndexes = [];

            foreach ($active as $position => $index) {

                $target = $share;

                if ($position < $extraPaisa) {
                    $target++;
                }

                if ($capacities[$index] < $target) {
                    $cappedIndexes[] = $index;
                }
            }

            /*
             * কোনো দিনের বকেয়া কম হলে:
             * সেটি সম্পূর্ণ মওকুফ করে অবশিষ্ট টাকা
             * অন্য দিনগুলোতে নতুন করে ভাগ করা হবে।
             */
            if (!empty($cappedIndexes)) {

                foreach ($cappedIndexes as $index) {

                    $amount = $capacities[$index];

                    $allocated[$index] += $amount;

                    $remaining -= $amount;

                    $capacities[$index] = 0;
                }

                continue;
            }

            /*
             * কোনো দিনের বকেয়া কম না হলে
             * সমানভাবে বণ্টন সম্পন্ন।
             */
            foreach ($active as $position => $index) {

                $amount = $share;

                /*
                 * অবশিষ্ট পয়সা কালানুক্রমে বণ্টন।
                 */
                if ($position < $extraPaisa) {
                    $amount++;
                }

                $allocated[$index] += $amount;

                $capacities[$index] -= $amount;

                $remaining -= $amount;
            }
        }

        /*
         * যেসব Ledger-এ মওকুফ হয়েছে
         * শুধু সেগুলো Return করা।
         */
        $selected = [];

        foreach ($outstanding as $index => $item) {

            if ($allocated[$index] <= 0) {
                continue;
            }

            $selected[] = [
                'ledger' => $item['ledger'],
                'due' => $item['due'],
                'waived' => $item['waived'],
                'base' => $item['base'],
                'paid' => $item['paid'],
                'amount' => $allocated[$index],
            ];
        }

        return $selected;
    }

    /**
     * টাকা Decimal থেকে পয়সায় রূপান্তর।
     *
     * Float arithmetic ব্যবহার করা হয় না।
     */
    private function toCents(
        string|int|float|null $amount
    ): int {

        if ($amount === null || $amount === '') {
            return 0;
        }

        $formatted = trim((string) $amount);

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $formatted)) {
            throw new RuntimeException(
                "Invalid monetary amount: {$formatted}"
            );
        }

        [$whole, $fraction] = array_pad(
            explode('.', $formatted, 2),
            2,
            '0'
        );

        $fraction = str_pad($fraction, 2, '0');

        return ((int) $whole * 100) + (int) $fraction;
    }

    /**
     * পয়সা থেকে Decimal টাকা।
     */
    private function fromCents(int $cents): string
    {
        return sprintf(
            '%d.%02d',
            intdiv($cents, 100),
            $cents % 100
        );
    }
}
