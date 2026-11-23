<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DailyAttendance;
use App\Models\DailyRentLedger;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Plot;
use App\Models\PlotAllocation;
use App\Models\PlotApplication;
use App\Services\GatewayException;
use App\Services\JanataPayClient;
use App\Services\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InvalidArgumentException;

class PaymentController extends Controller
{
   public function success(
        Request $request,
        JanataPayClient $gateway
    ): View {
        return $this->callback($request, 'success', $gateway);
    }

    /**
     * Payment failed callback.
     */
    public function failure(
        Request $request,
        JanataPayClient $gateway
    ): View {
        return $this->callback($request, 'failure', $gateway);
    }

    /**
     * Payment cancelled callback.
     */
    public function cancel(
        Request $request,
        JanataPayClient $gateway
    ): View {
        return $this->callback($request, 'cancel', $gateway);
    }

    /**
     * Common callback handler.
     */
    
    private function callback(
        Request $request,
        string $outcome,
        JanataPayClient $gateway
    ): View {
        $referenceId = trim(
            (string) $request->query('refid', '')
        );

        if ($referenceId === '') {
            abort(400, 'Missing payment reference.');
        }

        $payment = Payment::query()
            ->where('merchant_invoice_id', $referenceId)
            ->first();

        // Payment reference not found.
        if (! $payment) {
            abort(404, 'Payment record not found.');
        }

        // Log::info('JanataPay Callback Received', [
        //     'payment_id' => $payment->id,
        //     'merchant_invoice_id' => $payment->merchant_invoice_id,
        //     'payment_for' => $payment->payment_for,
        //     'outcome' => $outcome,
        // ]);

        try {
            $this->verifyAndSave($payment, $gateway);
        } catch (\Throwable $exception) {
            Log::error('JanataPay payment verification error.', [
                'payment_id' => $payment->id,
                'merchant_invoice_id' => $payment->merchant_invoice_id,
                'payment_for' => $payment->payment_for,
                'message' => $exception->getMessage(),
            ]);
        }

        $payment->refresh();

        // Gateway verification succeeded.
        if (
            $payment->status === 'success'
            && $payment->verified_at !== null
        ) {
            if (in_array($payment->payment_for, [
                    'application_fee',
                    'security_deposit',
                ], true)) {

                    if (! $payment->application_id) {
                        return view('frontend.pages.payment.failure', [
                            'payment' => $payment,
                            'message' => 'আবেদনের তথ্য পাওয়া যায়নি।',
                        ]);
                    }

                    $application = PlotApplication::query()
                        ->with(['applicant', 'plot', 'allocation'])
                        ->findOrFail($payment->application_id);

                    return view('frontend.pages.payment.success', [
                        'payment' => $payment,
                        'application' => $application,
                        'allocation' => null,
                    ]);
                }

                if ($payment->payment_for === 'daily_rent') {

                    if (! $payment->allocation_id) {
                        return view('frontend.pages.payment.failure', [
                            'payment' => $payment,
                            'message' => 'প্লট বরাদ্দের তথ্য পাওয়া যায়নি।',
                        ]);
                    }

                    $allocation = PlotAllocation::query()
                        ->with(['applicant', 'plot'])
                        ->findOrFail($payment->allocation_id);

                    // এই Payment-এর সঙ্গে যুক্ত প্রতিটি Daily Rent Ledger লোড।
                    $payment->load('paymentAllocations.payable');

                    return view('frontend.pages.payment.success', [
                        'payment' => $payment,
                        'application' => null,
                        'allocation' => $allocation,
                    ]);
                }

            // Other payment types will be handled by their respective success views.
            Log::warning('No success view configured for this payment type.', [
                'payment_id' => $payment->id,
                'payment_for' => $payment->payment_for,
            ]);
        }

        // Payment was cancelled.
        if ($outcome === 'cancel') {
            return view('frontend.pages.payment.cancel');
        }

        return view('frontend.pages.payment.failure', [
            'message' => 'দুঃখিত, আপনার পেমেন্ট সফল হয়নি। অনুগ্রহ করে পেমেন্টের অবস্থা যাচাই করে পুনরায় চেষ্টা করুন।',
        ]);
    }


        /**
         * Verify payment with JanataPay and save the gateway result.
         *
         * Gateway verification and application reconciliation are separate
         * database transactions. A reconciliation failure must never roll
         * back a successfully verified gateway payment.
         */
        private function verifyAndSave(
            Payment $payment,
            JanataPayClient $gateway
        ): void {
            $payment->refresh();

            // Already verified: retry reconciliation if it is incomplete.
            if (
                $payment->status === 'success'
                && $payment->verified_at !== null
            ) {
                $this->reconcilePayment($payment);

                return;
            }

            if (blank($payment->transaction_token)) {
                throw new GatewayException(
                    'Payment transaction token is missing.'
                );
            }

            // Verify with JanataPay outside the database transaction.
            $response = $gateway->verify(
                $payment->merchant_invoice_id,
                $payment->transaction_token
            );

            $data = $response['data'] ?? [];

            if (! is_array($data) || empty($data)) {
                throw new GatewayException(
                    'JanataPay verification response data is missing.'
                );
            }

            $statusCode = (string) (
                $data['transactionStatusCode'] ?? ''
            );

            $gatewayStatus = strtolower(trim((string) (
                $data['transactionStatus'] ?? 'unknown'
            )));

            $ftNumber = trim(
                (string) ($data['ftNumber'] ?? '')
            );

            try {
                $paidCents = Money::cents($data['amount'] ?? null);
            } catch (InvalidArgumentException) {
                $paidCents = 0;
            }

            $expectedCents = Money::cents($payment->amount);

            $confirmed = (
                $statusCode === '1003'
                && $gatewayStatus === 'success'
                && $paidCents === $expectedCents
            );

            $databaseStatus = match ($gatewayStatus) {
                'failed' => 'failed',
                'canceled' => 'canceled',
                'refunded' => 'refunded',
                default => 'pending',
            };

            // First transaction: permanently save the gateway result.
            DB::transaction(function () use (
                $payment,
                $response,
                $statusCode,
                $gatewayStatus,
                $ftNumber,
                $confirmed,
                $databaseStatus
            ) {
                $lockedPayment = Payment::query()
                    ->whereKey($payment->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Another callback may already have verified this payment.
                if (
                    $lockedPayment->status === 'success'
                    && $lockedPayment->verified_at !== null
                ) {
                    return;
                }

                if ($confirmed && $ftNumber !== '') {
                    $duplicate = Payment::query()
                        ->where('gateway_transaction_id', $ftNumber)
                        ->where('id', '!=', $lockedPayment->id)
                        ->exists();

                    if ($duplicate) {
                        throw new GatewayException(
                            'This JanataPay transaction ID is already used.'
                        );
                    }
                }

                $updateData = [
                    'gateway_response' => $response,
                    'verified_at' => now('Asia/Dhaka'),
                    'status' => $databaseStatus,
                    'remarks' => 'Gateway verification completed. Status: '
                        . $gatewayStatus
                        . ' (Code: '
                        . $statusCode
                        . ')',
                ];

                if ($confirmed) {
                    $updateData['status'] = 'success';
                    $updateData['paid_at'] = now('Asia/Dhaka');
                    $updateData['reconciliation_status'] = 'pending';
                    $updateData['reconciliation_error'] = null;
                    $updateData['remarks'] =
                        'JanataPay payment successfully verified.';

                    if ($ftNumber !== '') {
                        $updateData['gateway_transaction_id'] = $ftNumber;
                    }
                }

                $lockedPayment->update($updateData);
            }, 5);

            // Second step: reconcile only after gateway success is committed.
            $payment->refresh();

            if (
                $payment->status === 'success'
                && $payment->verified_at !== null
            ) {
                $this->reconcilePayment($payment);
            }
        }


        /**
         * Reconcile a successfully verified payment with application records.
         *
         * This runs in a separate transaction from gateway verification.
         */
        private function reconcilePayment(Payment $payment): void
        {
            $payment->refresh();

            if (
                $payment->status !== 'success'
                || $payment->verified_at === null
            ) {
                return;
            }

            if ($payment->reconciliation_status === 'completed') {
                return;
            }

            // Count each reconciliation attempt separately.
            Payment::query()
                ->whereKey($payment->id)
                ->increment('reconciliation_attempts');

            try {
                DB::transaction(function () use ($payment) {

                    $lockedPayment = Payment::query()
                        ->whereKey($payment->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (
                        $lockedPayment->status !== 'success'
                        || $lockedPayment->verified_at === null
                    ) {
                        throw new GatewayException(
                            'Payment is not successfully verified.'
                        );
                    }

                    if (
                        $lockedPayment->reconciliation_status === 'completed'
                    ) {
                        return;
                    }

                    $lockedPayment->update([
                        'reconciliation_status' => 'pending',
                        'reconciliation_error' => null,
                    ]);

                    /*
                    * Daily Rent Reconciliation
                    */
                    if ($lockedPayment->payment_for === 'daily_rent') {
                        $this->reconcileDailyRent($lockedPayment);
                    }

                    /*
                    * Application Fee Reconciliation
                    */
                    elseif ($lockedPayment->payment_for === 'application_fee') {

                        if (! $lockedPayment->application_id) {
                            throw new GatewayException(
                                'Application fee payment has no application reference.'
                            );
                        }

                        $application = PlotApplication::query()
                            ->whereKey($lockedPayment->application_id)
                            ->lockForUpdate()
                            ->firstOrFail();

                        if ($application->status === 'pending_payment') {
                            $application->update([
                                'status' => 'submitted',
                            ]);
                        } elseif ($application->status !== 'submitted') {
                            throw new GatewayException(
                                'Application is not in a valid state for fee reconciliation.'
                            );
                        }
                    }

                    /*
                    * Security Deposit Reconciliation
                    */
                    elseif ($lockedPayment->payment_for === 'security_deposit') {

                        if (! $lockedPayment->application_id) {
                            throw new GatewayException(
                                'Security deposit payment has no application reference.'
                            );
                        }

                        $application = PlotApplication::query()
                            ->whereKey($lockedPayment->application_id)
                            ->lockForUpdate()
                            ->firstOrFail();

                        if (! in_array($application->status, [
                            'approved',
                            'allocated',
                        ], true)) {
                            throw new GatewayException(
                                'Application is not approved for plot allocation.'
                            );
                        }

                        $existingAllocation = PlotAllocation::query()
                            ->where('application_id', $application->id)
                            ->where('status', 'active')
                            ->lockForUpdate()
                            ->first();

                        if ($existingAllocation) {

                            if (
                                (int) $existingAllocation->applicant_id !==
                                    (int) $application->applicant_id
                                || (int) $existingAllocation->plot_id !==
                                    (int) $application->plot_id
                            ) {
                                throw new GatewayException(
                                    'An active allocation already exists with different details.'
                                );
                            }

                            Plot::query()
                                ->whereKey($existingAllocation->plot_id)
                                ->lockForUpdate()
                                ->firstOrFail()
                                ->update([
                                    'status' => 'allocated',
                                ]);

                            $application->update([
                                'status' => 'allocated',
                            ]);

                        } else {

                            $plot = Plot::query()
                                ->whereKey($application->plot_id)
                                ->lockForUpdate()
                                ->firstOrFail();

                            if ($plot->status !== 'available') {
                                throw new GatewayException(
                                    'The selected plot is no longer available.'
                                );
                            }

                            PlotAllocation::query()->create([
                                'application_id' => $application->id,
                                'applicant_id' => $application->applicant_id,
                                'plot_id' => $plot->id,
                                'active_applicant_id' => $application->applicant_id,
                                'active_plot_id' => $plot->id,
                                'allocated_by' => null,
                                'allocated_at' => now('Asia/Dhaka')->toDateString(),
                                'start_date' => now('Asia/Dhaka')->toDateString(),
                                'end_date' => null,
                                'status' => 'active',
                                'remarks' => 'Automatically allocated after successful security deposit payment.',
                            ]);

                            $plot->update([
                                'status' => 'allocated',
                            ]);

                            $application->update([
                                'status' => 'allocated',
                            ]);
                        }
                    }

                    /*
                    * Unsupported payment type
                    */
                    else {
                        throw new GatewayException(
                            'No reconciliation handler exists for this payment type.'
                        );
                    }

                    // Only mark completed after all reconciliation work succeeds.
                    $lockedPayment->update([
                        'reconciliation_status' => 'completed',
                        'reconciled_at' => now('Asia/Dhaka'),
                        'reconciliation_error' => null,
                    ]);
                }, 5);

            } catch (\Throwable $exception) {

                // This update is outside the failed reconciliation transaction.
                Payment::query()
                    ->whereKey($payment->id)
                    ->where('status', 'success')
                    ->update([
                        'reconciliation_status' => 'failed',
                        'reconciliation_error' => $exception->getMessage(),
                    ]);

                Log::error('Payment reconciliation failed.', [
                    'payment_id' => $payment->id,
                    'payment_for' => $payment->payment_for,
                    'message' => $exception->getMessage(),
                ]);
            }
        }


        /**
         * Reconcile Daily Rent Payment Allocations.
         *
         * Every selected day must be paid in full.
         * No partial daily payment is accepted.
         */
        private function reconcileDailyRent(Payment $payment): void
        {
            if (! $payment->allocation_id) {
                throw new GatewayException(
                    'Daily rent payment has no allocation reference.'
                );
            }

            $allocation = PlotAllocation::query()
                ->whereKey($payment->allocation_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                (int) $allocation->applicant_id !==
                (int) $payment->applicant_id
            ) {
                throw new GatewayException(
                    'Payment applicant does not match allocation applicant.'
                );
            }

            $paymentAllocations = PaymentAllocation::query()
                ->where('payment_id', $payment->id)
                ->orderBy('id')
                ->get();

            if ($paymentAllocations->isEmpty()) {
                throw new GatewayException(
                    'No daily rent payment allocations were found.'
                );
            }

            $expectedTotalCents = Money::cents($payment->amount);
            $allocatedTotalCents = 0;
            $processedLedgerIds = [];

            foreach ($paymentAllocations as $paymentAllocation) {

                $allocatedCents = Money::cents(
                    $paymentAllocation->amount
                );

                if ($allocatedCents <= 0) {
                    throw new GatewayException(
                        'Invalid payment allocation amount.'
                    );
                }

                $allocatedTotalCents += $allocatedCents;

                if (
                    $paymentAllocation->payable_type !==
                    (new DailyRentLedger())->getMorphClass()
                ) {
                    throw new GatewayException(
                        'Invalid payable type in daily rent payment allocation.'
                    );
                }

                $ledgerId = (int) $paymentAllocation->payable_id;

                // The same Ledger cannot appear twice in one Payment.
                if (in_array($ledgerId, $processedLedgerIds, true)) {
                    throw new GatewayException(
                        'Duplicate daily rent ledger allocation detected.'
                    );
                }

                $processedLedgerIds[] = $ledgerId;

                $ledger = DailyRentLedger::query()
                    ->whereKey($ledgerId)
                    ->where('allocation_id', $allocation->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Approved waivers only.
                $approvedWaiver = $ledger->adjustments()
                    ->where('status', 'approved')
                    ->sum('approved_amount');

                $baseCents = Money::cents($ledger->base_amount);

                $waivedCents = min(
                    $baseCents,
                    max(0, Money::cents($approvedWaiver))
                );

                $netCents = max(
                    0,
                    $baseCents - $waivedCents
                );

                /*
                * Calculate previously paid amounts, excluding this Payment.
                * This prevents the current Payment from being counted twice
                * when checking the outstanding balance.
                */
                $previousPaidAmount = PaymentAllocation::query()
                    ->where('payable_type', $ledger->getMorphClass())
                    ->where('payable_id', $ledger->id)
                    ->where('payment_id', '!=', $payment->id)
                    ->whereHas('payment', function ($query) {
                        $query->where('status', 'success')
                            ->whereNotNull('verified_at');
                    })
                    ->sum('amount');

                $previousPaidCents = Money::cents($previousPaidAmount);

                if ($previousPaidCents > $netCents) {
                    throw new GatewayException(
                        'Previously paid amount exceeds the current payable rent.'
                    );
                }

                $actualDueCents = $netCents - $previousPaidCents;

                // The allocated amount must equal the entire outstanding amount.
                if (
                    $actualDueCents <= 0
                    || $allocatedCents !== $actualDueCents
                ) {
                    throw new GatewayException(
                        'Daily rent allocation does not match the current outstanding amount for rent date '
                        . $ledger->rent_date->toDateString()
                    );
                }

                // Current Payment plus previous verified payments.
                $totalPaidCents = $previousPaidCents + $allocatedCents;

                if ($totalPaidCents !== $netCents) {
                    throw new GatewayException(
                        'Daily rent ledger payment reconciliation mismatch.'
                    );
                }

                $ledger->update([
                    'adjustment_amount' => number_format(
                        $waivedCents / 100,
                        2,
                        '.',
                        ''
                    ),
                    'net_amount' => number_format(
                        $netCents / 100,
                        2,
                        '.',
                        ''
                    ),
                    'status' => $netCents <= 0 ? 'waived' : 'paid',
                ]);

                // Only today's fully paid Ledger changes today's Attendance.
                if (
                    $netCents > 0
                    && $ledger->rent_date->toDateString() ===
                        now('Asia/Dhaka')->toDateString()
                    && $ledger->attendance_id
                ) {
                    DailyAttendance::query()
                        ->whereKey($ledger->attendance_id)
                        ->update([
                            'attendance_status' => 'present',
                            'marked_at' => now('Asia/Dhaka'),
                        ]);
                }
            }

            if ($allocatedTotalCents !== $expectedTotalCents) {
                throw new GatewayException(
                    'Payment allocation total does not match the verified payment amount.'
                );
            }
        }



        
    // /**
    //  * Verify payment with JanataPay and save the result.
    //  */
    // private function verifyAndSave(
    //     Payment $payment,
    //     JanataPayClient $gateway
    // ): void {
    //     if (
    //         $payment->status === 'success'
    //         && $payment->verified_at !== null
    //     ) {
    //         return;
    //     }

    //     if (blank($payment->transaction_token)) {
    //         throw new GatewayException(
    //             'Payment transaction token is missing.'
    //         );
    //     }

    //     // Verify payment with JanataPay.
    //     $response = $gateway->verify(
    //         $payment->merchant_invoice_id,
    //         $payment->transaction_token
    //     );

    //     $data = $response['data'] ?? [];

    //     // Log::info('JanataPay Verification Response', [
    //     //     'payment_id' => $payment->id,
    //     //     'merchant_invoice_id' => $payment->merchant_invoice_id,
    //     //     'response' => $response,
    //     // ]);

    //     if (! is_array($data) || empty($data)) {
    //         throw new GatewayException(
    //             'JanataPay verification response data is missing.'
    //         );
    //     }

    //     $statusCode = (string) (
    //         $data['transactionStatusCode'] ?? ''
    //     );

    //     $gatewayStatus = strtolower(trim((string) (
    //         $data['transactionStatus'] ?? 'unknown'
    //     )));

    //     $ftNumber = trim(
    //         (string) ($data['ftNumber'] ?? '')
    //     );

    //     try {
    //         $paidCents = Money::cents(
    //             $data['amount'] ?? null
    //         );
    //     } catch (InvalidArgumentException $exception) {
    //         $paidCents = 0;
    //     }

    //     $expectedCents = Money::cents($payment->amount);

    //     // Confirm successful transaction with exact amount.
    //     $confirmed = (
    //         $statusCode === '1003'
    //         && $gatewayStatus === 'success'
    //         && $paidCents === $expectedCents
    //     );

    //     // Map gateway status to our database ENUM values.
    //     $databaseStatus = match ($gatewayStatus) {
    //         'failed' => 'failed',
    //         'cancelled', 'canceled' => 'cancelled',
    //         'refunded' => 'refunded',
    //         default => 'pending',
    //     };

    //     DB::transaction(function () use (
    //         $payment,
    //         $response,
    //         $statusCode,
    //         $gatewayStatus,
    //         $ftNumber,
    //         $confirmed,
    //         $databaseStatus
    //     ) {
    //         /*
    //         * Lock payment record.
    //         */
    //         $lockedPayment = Payment::query()
    //             ->whereKey($payment->id)
    //             ->lockForUpdate()
    //             ->firstOrFail();

    //         // Prevent duplicate callback processing.
    //         if (
    //             $lockedPayment->status === 'success'
    //             && $lockedPayment->verified_at !== null
    //         ) {
    //             return;
    //         }

    //         /*
    //         * Check duplicate gateway transaction ID.
    //         */
    //         if ($confirmed && $ftNumber !== '') {
    //             $duplicate = Payment::query()
    //                 ->where('gateway_transaction_id', $ftNumber)
    //                 ->where('id', '!=', $lockedPayment->id)
    //                 ->exists();

    //             if ($duplicate) {
    //                 throw new GatewayException(
    //                     'This JanataPay transaction ID is already used.'
    //                 );
    //             }
    //         }

    //         /*
    //         * Prepare payment verification data.
    //         */
    //         $updateData = [
    //             'gateway_response' => $response,
    //             'verified_at' => now('Asia/Dhaka'),
    //             'status' => $databaseStatus,
    //             'remarks' => 'Payment verification completed. Gateway status: '
    //                 . $gatewayStatus
    //                 . ' (Code: '
    //                 . $statusCode
    //                 . ')',
    //         ];

    //         if (! $confirmed) {
    //             $lockedPayment->update($updateData);

    //             return;
    //         }

    //         $updateData['status'] = 'success';
    //         $updateData['paid_at'] = now('Asia/Dhaka');
    //         $updateData['remarks'] = 'JanataPay payment successfully verified.';

    //         if ($ftNumber !== '') {
    //             $updateData['gateway_transaction_id'] = $ftNumber;
    //         }

    //         $lockedPayment->update($updateData);


    //         // Daily Rent Payment Processing
    //         if ($lockedPayment->payment_for === 'daily_rent') {

    //             if (! $lockedPayment->allocation_id) {
    //                 throw new \RuntimeException(
    //                     'Daily rent payment has no allocation reference.'
    //                 );
    //             }

    //             $lockedAllocation = PlotAllocation::query()
    //                 ->whereKey($lockedPayment->allocation_id)
    //                 ->lockForUpdate()
    //                 ->firstOrFail();

    //             if (
    //                 (int) $lockedAllocation->applicant_id !==
    //                 (int) $lockedPayment->applicant_id
    //             ) {
    //                 throw new \RuntimeException(
    //                     'Payment applicant does not match allocation applicant.'
    //                 );
    //             }

    //             // এই Payment-এর সঙ্গে যুক্ত Ledger-গুলো সংগ্রহ।
    //             $paymentAllocations = PaymentAllocation::query()
    //                 ->where('payment_id', $lockedPayment->id)
    //                 ->get();

    //             if ($paymentAllocations->isEmpty()) {
    //                 throw new \RuntimeException(
    //                     'No daily rent ledger allocation found for this payment.'
    //                 );
    //             }

    //             // Payment Allocation-এর মোট টাকা Payment-এর সমান কি না যাচাই।
    //             $allocatedCents = 0;

    //             foreach ($paymentAllocations as $paymentAllocation) {
    //                 $allocatedCents += Money::cents(
    //                     $paymentAllocation->amount
    //                 );
    //             }

    //             if ($allocatedCents !== Money::cents($lockedPayment->amount)) {
    //                 throw new \RuntimeException(
    //                     'Payment allocation total does not match payment amount.'
    //                 );
    //             }

    //             foreach ($paymentAllocations as $paymentAllocation) {

    //                 // ভুল ধরনের Payable গ্রহণ করা যাবে না।
    //                 if (
    //                     $paymentAllocation->payable_type !==
    //                     (new DailyRentLedger())->getMorphClass()
    //                 ) {
    //                     throw new \RuntimeException(
    //                         'Invalid payable type in daily rent payment allocation.'
    //                     );
    //                 }

    //                 // সংশ্লিষ্ট Ledger Lock করা।
    //                 $ledger = DailyRentLedger::query()
    //                     ->whereKey($paymentAllocation->payable_id)
    //                     ->where('allocation_id', $lockedAllocation->id)
    //                     ->lockForUpdate()
    //                     ->firstOrFail();

    //                 // শুধু Approved RentAdjustment মওকুফ হিসেবে গণনা হবে।
    //                 $approvedWaiver = $ledger->adjustments()
    //                     ->where('status', 'approved')
    //                     ->sum('approved_amount');

    //                 $baseCents = Money::cents($ledger->base_amount);

    //                 $waivedCents = min(
    //                     $baseCents,
    //                     max(0, Money::cents($approvedWaiver))
    //                 );

    //                 $netCents = max(
    //                     0,
    //                     $baseCents - $waivedCents
    //                 );

    //                 // শুধু সফল ও Verified Payment-এর টাকা গণনা।
    //                 // বর্তমান Payment-ও এখন success হওয়ায় এতে অন্তর্ভুক্ত হবে।
    //                 $paidAmount = PaymentAllocation::query()
    //                     ->where('payable_type', $ledger->getMorphClass())
    //                     ->where('payable_id', $ledger->id)
    //                     ->whereHas('payment', function ($query) {
    //                         $query->where('status', 'success')
    //                             ->whereNotNull('verified_at');
    //                     })
    //                     ->sum('amount');

    //                 $paidCents = Money::cents($paidAmount);

    //                 // Ledger-এর আর্থিক হিসাব আপডেট।
    //                 if ($netCents <= 0) {
    //                     $ledgerStatus = 'waived';
    //                 } elseif ($paidCents >= $netCents) {
    //                     $ledgerStatus = 'paid';
    //                 } else {
    //                     $ledgerStatus = 'pending';
    //                 }

    //                 $ledger->update([
    //                     'adjustment_amount' => number_format(
    //                         $waivedCents / 100,
    //                         2,
    //                         '.',
    //                         ''
    //                     ),
    //                     'net_amount' => number_format(
    //                         $netCents / 100,
    //                         2,
    //                         '.',
    //                         ''
    //                     ),
    //                     'status' => $ledgerStatus,
    //                 ]);

    //                 // শুধু আজকের Ledger সম্পূর্ণ Paid হলে আজকের Attendance Present হবে।
    //                 if (
    //                     $ledgerStatus === 'paid' &&
    //                     $ledger->rent_date->toDateString() ===
    //                         now('Asia/Dhaka')->toDateString() &&
    //                     $ledger->attendance_id
    //                 ) {
    //                     DailyAttendance::query()
    //                         ->whereKey($ledger->attendance_id)
    //                         ->update([
    //                             'attendance_status' => 'present',
    //                             'marked_at' => now('Asia/Dhaka'),
    //                         ]);
    //                 }
    //             }

    //             return;
    //         }


    //         /*
    //         * Application Fee:
    //         * Move application to submitted status.
    //         */
    //         if (
    //             $lockedPayment->payment_for === 'application_fee'
    //             && $lockedPayment->application_id !== null
    //         ) {
    //             PlotApplication::query()
    //                 ->whereKey($lockedPayment->application_id)
    //                 ->where('status', 'pending_payment')
    //                 ->update([
    //                     'status' => 'submitted',
    //                 ]);

    //             return;
    //         }

    //         /*
    //         * Security Deposit:
    //         * Automatically create Plot Allocation.
    //         */
    //         if (
    //             $lockedPayment->payment_for === 'security_deposit'
    //             && $lockedPayment->application_id !== null
    //         ) {
    //             // Lock application.
    //             $application = PlotApplication::query()
    //                 ->whereKey($lockedPayment->application_id)
    //                 ->lockForUpdate()
    //                 ->firstOrFail();

    //             if (! in_array($application->status, [
    //                 'approved',
    //                 'allocated',
    //             ], true)) {
    //                 throw new GatewayException(
    //                     'Application is not approved for plot allocation.'
    //                 );
    //             }

    //             /*
    //             * Check existing active allocation.
    //             * This also makes repeated processing safe.
    //             */
    //             $existingAllocation = PlotAllocation::query()
    //                 ->where('application_id', $application->id)
    //                 ->where('status', 'active')
    //                 ->lockForUpdate()
    //                 ->first();

    //             if ($existingAllocation) {
    //                 if (
    //                     $existingAllocation->applicant_id !== $application->applicant_id
    //                     || $existingAllocation->plot_id !== $application->plot_id
    //                 ) {
    //                     throw new GatewayException(
    //                         'An active allocation already exists with different details.'
    //                     );
    //                 }

    //                 // Keep application and plot status consistent.
    //                 Plot::query()
    //                     ->whereKey($existingAllocation->plot_id)
    //                     ->lockForUpdate()
    //                     ->firstOrFail()
    //                     ->update([
    //                         'status' => 'allocated',
    //                     ]);

    //                 $application->update([
    //                     'status' => 'allocated',
    //                 ]);

    //                 return;
    //             }

    //             // Lock the final plot selected in the application.
    //             $plot = Plot::query()
    //                 ->whereKey($application->plot_id)
    //                 ->lockForUpdate()
    //                 ->firstOrFail();

    //             if ($plot->status !== 'available') {
    //                 throw new GatewayException(
    //                     'The selected plot is no longer available. Please contact the office.'
    //                 );
    //             }

    //             /*
    //             * Create allocation.
    //             */
    //             PlotAllocation::create([
    //                 'application_id' => $application->id,
    //                 'applicant_id' => $application->applicant_id,
    //                 'plot_id' => $plot->id,
    //                 'active_applicant_id' => $application->applicant_id,
    //                 'active_plot_id' => $plot->id,
    //                 'allocated_by' => null,
    //                 'allocated_at' => now('Asia/Dhaka')->toDateString(),
    //                 'start_date' => now('Asia/Dhaka')->toDateString(),
    //                 'end_date' => null,
    //                 'status' => 'active',
    //                 'remarks' => 'Automatically allocated after successful security deposit payment.',
    //             ]);

    //             /*
    //             * Update plot and application status.
    //             */
    //             $plot->update([
    //                 'status' => 'allocated',
    //             ]);

    //             $application->update([
    //                 'status' => 'allocated',
    //             ]);
    //         }
    //     }, 5);
    // }

}
