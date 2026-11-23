<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DailyRentLedger;
use App\Models\Payment;
use App\Models\Plot;
use App\Models\PlotAllocation;
use App\Services\DailyRentLedgerService;
use App\Services\JanataPayClient;
use App\Services\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class DailyRentSearchController extends Controller
{
        public function search(
        Request $request,
        DailyRentLedgerService $ledgerService
    ) {
        $validated = $request->validate([
            'plot_number' => ['required', 'string', 'max:50'],
        ]);

        $plotNumber = mb_strtoupper(trim($validated['plot_number']));

        $plot = Plot::query()
            ->where('plot_number', $plotNumber)
            ->first();

        $allocation = null;
        $dueLedgers = collect();

        $totalRent = 0;
        $totalWaived = 0;
        $totalPaid = 0;
        $totalDue = 0;
        $dueDays = 0;

        $syncIncomplete = false;
        $syncBlockedDate = null;

        if ($plot) {
            $allocation = PlotAllocation::query()
                ->with(['applicant', 'plot'])
                ->where('plot_id', $plot->id)
                ->where('status', 'active')
                ->first();
        }

        if ($allocation && $allocation->start_date) {

            // Synchronize missing Attendance and Daily Rent Ledger.
            $syncResult = $ledgerService->syncAllocation($allocation);

            if ($syncResult['status'] === 'rate_missing') {
                $syncIncomplete = true;
                $syncBlockedDate = $syncResult['blocked_date'];
            }

            $rentStartDate = $allocation->start_date
                ->copy()
                ->addDay()
                ->toDateString();

            $today = now('Asia/Dhaka')->toDateString();

            $rentEndDate = $today;

            if (
                $allocation->end_date &&
                $allocation->end_date->toDateString() < $today
            ) {
                $rentEndDate = $allocation->end_date->toDateString();
            }

            /*
             * সব Ledger আনতে হবে।
             * সম্পূর্ণ মওকুফ ও সম্পূর্ণ পরিশোধিত Ledger-ও দেখাতে হবে।
             */
            $dueLedgers = DailyRentLedger::query()
                ->where('allocation_id', $allocation->id)
                ->whereDate('rent_date', '>=', $rentStartDate)
                ->whereDate('rent_date', '<=', $rentEndDate)
                ->with([
                    'adjustments' => function ($query) {
                        $query->where('status', 'approved');
                    },
                    'paymentAllocations' => function ($query) {
                        $query->whereHas('payment', function ($paymentQuery) {
                            $paymentQuery
                                ->where('status', 'success')
                                ->whereNotNull('verified_at');
                        });
                    },
                ])
                ->orderBy('rent_date')
                ->get();

            foreach ($dueLedgers as $ledger) {

                $baseAmount = (float) $ledger->base_amount;

                // Only approved adjustments count as rent waiver.
                $waivedAmount = (float) $ledger->adjustments
                    ->sum('approved_amount');

                // Waiver cannot exceed the original daily rent.
                $waivedAmount = min(
                    $baseAmount,
                    max(0, $waivedAmount)
                );

                $payableAmount = max(
                    0,
                    $baseAmount - $waivedAmount
                );

                // Only successful and verified payments count.
                $paidAmount = (float) $ledger->paymentAllocations
                    ->sum('amount');

                $dueAmount = max(
                    0,
                    $payableAmount - $paidAmount
                );

                $ledger->setAttribute(
                    'calculated_waived_amount',
                    $waivedAmount
                );

                $ledger->setAttribute(
                    'calculated_payable_amount',
                    $payableAmount
                );

                $ledger->setAttribute(
                    'calculated_paid_amount',
                    $paidAmount
                );

                $ledger->setAttribute(
                    'calculated_due_amount',
                    $dueAmount
                );

                if ($waivedAmount >= $baseAmount && $baseAmount > 0) {
                    $displayStatus = 'waived';
                } elseif ($dueAmount <= 0) {
                    $displayStatus = 'paid';
                } elseif ($waivedAmount > 0) {
                    $displayStatus = 'partially_waived';
                } else {
                    $displayStatus = 'pending';
                }

                $ledger->setAttribute(
                    'calculated_display_status',
                    $displayStatus
                );

                $totalRent += $baseAmount;
                $totalWaived += $waivedAmount;
                $totalPaid += $paidAmount;

                if ($dueAmount > 0) {
                    $dueDays++;
                }
            }

            // Do not show an incomplete amount as the final total.
            $totalDue = $syncIncomplete
                ? null
                : (float) $dueLedgers->sum(
                    'calculated_due_amount'
                );
        }

        return view('frontend.pages.daily-rent-search-result', [
            'plotNumber' => $plotNumber,
            'plot' => $plot,
            'allocation' => $allocation,
            'dueLedgers' => $dueLedgers,
            'dueDays' => $dueDays,
            'totalRent' => $totalRent,
            'totalWaived' => $totalWaived,
            'totalDue' => $totalDue,
            'syncIncomplete' => $syncIncomplete,
            'syncBlockedDate' => $syncBlockedDate,
            'totalPaid' => $totalPaid,
        ]);
    }



    public function pay(
        Request $request,
        DailyRentLedgerService $ledgerService,
        JanataPayClient $janataPay
    ) {
        $validated = $request->validate([
            'plot_number' => ['required', 'string', 'max:50'],
            'days' => ['required', 'integer', 'min:1'],
        ]);

        $plotNumber = mb_strtoupper(trim($validated['plot_number']));
        $requestedDays = (int) $validated['days'];

        $plot = Plot::query()
            ->where('plot_number', $plotNumber)
            ->first();

        if (! $plot) {
            return back()->withErrors([
                'plot_number' => 'প্লট নম্বর সঠিক নয়।',
            ])->withInput();
        }

        $allocation = PlotAllocation::query()
            ->where('plot_id', $plot->id)
            ->where('status', 'active')
            ->first();

        if (! $allocation || ! $allocation->start_date) {
            return back()->withErrors([
                'plot_number' => 'এই প্লটের কোনো সক্রিয় বরাদ্দ পাওয়া যায়নি।',
            ])->withInput();
        }

        // প্রথমে এই Allocation-এর অনুপস্থিত Attendance ও Ledger তৈরি করি।
        $syncResult = $ledgerService->syncAllocation($allocation);

        if (($syncResult['status'] ?? null) !== 'completed') {
            return back()->withErrors([
                'payment' => 'দৈনিক ভাড়ার হিসাব সম্পূর্ণ করা যায়নি। অনুগ্রহ করে অফিসে যোগাযোগ করুন।',
            ])->withInput();
        }

        $payment = null;

        try {
            $payment = DB::transaction(function () use (
                $allocation,
                $requestedDays
            ) {
                // একই Allocation-এর একাধিক পেমেন্ট শুরু হওয়া ঠেকানো।
                $lockedAllocation = PlotAllocation::query()
                    ->whereKey($allocation->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedAllocation->status !== 'active') {
                    throw new \RuntimeException(
                        'এই প্লটের বরাদ্দ বর্তমানে সক্রিয় নেই।'
                    );
                }

                // আগের কোনো পেমেন্ট এখনো সম্পন্ন না হলে নতুন পেমেন্ট নয়।
                $pendingPaymentExists = Payment::query()
                    ->where('allocation_id', $lockedAllocation->id)
                    ->where('payment_for', 'daily_rent')
                    ->whereIn('status', ['initiated', 'pending'])
                    ->exists();

                if ($pendingPaymentExists) {
                    throw new \RuntimeException(
                        'আপনার একটি দৈনিক ভাড়া পেমেন্ট এখনো সম্পন্ন হয়নি। আগে সেই পেমেন্টটি সম্পন্ন করুন।'
                    );
                }

                $rentStartDate = $lockedAllocation->start_date
                    ->copy()
                    ->addDay()
                    ->toDateString();

                $today = now('Asia/Dhaka')->toDateString();

                $rentEndDate = $today;

                if (
                    $lockedAllocation->end_date &&
                    $lockedAllocation->end_date->toDateString() < $today
                ) {
                    $rentEndDate = $lockedAllocation->end_date->toDateString();
                }

                // সব Ledger-এর অনুমোদিত মওকুফ ও যাচাইকৃত পেমেন্টসহ তথ্য আনি।
                $ledgers = DailyRentLedger::query()
                    ->where('allocation_id', $lockedAllocation->id)
                    ->whereDate('rent_date', '>=', $rentStartDate)
                    ->whereDate('rent_date', '<=', $rentEndDate)
                    ->with([
                        'adjustments' => function ($query) {
                            $query->where('status', 'approved');
                        },
                        'paymentAllocations' => function ($query) {
                            $query->whereHas('payment', function ($paymentQuery) {
                                $paymentQuery
                                    ->where('status', 'success')
                                    ->whereNotNull('verified_at');
                            });
                        },
                    ])
                    ->orderBy('rent_date')
                    ->lockForUpdate()
                    ->get();

                // প্রতিটি দিনের সম্পূর্ণ অবশিষ্ট বকেয়া হিসাব।
                $outstandingLedgers = $ledgers
                    ->map(function ($ledger) {
                        $baseAmount = Money::cents($ledger->base_amount);

                        $waivedAmount = (int) round(
                            (float) $ledger->adjustments->sum('approved_amount') * 100
                        );

                        $waivedAmount = min(
                            $baseAmount,
                            max(0, $waivedAmount)
                        );

                        $payableAmount = max(
                            0,
                            $baseAmount - $waivedAmount
                        );

                        $paidAmount = (int) round(
                            (float) $ledger->paymentAllocations->sum('amount') * 100
                        );

                        $dueAmount = max(
                            0,
                            $payableAmount - $paidAmount
                        );

                        $ledger->setAttribute(
                            'payment_due_cents',
                            $dueAmount
                        );

                        return $ledger;
                    })
                    ->filter(fn ($ledger) => $ledger->payment_due_cents > 0)
                    ->values();

                $outstandingDays = $outstandingLedgers->count();

                if ($outstandingDays === 0) {
                    throw new \RuntimeException(
                        'আপনার বর্তমানে কোনো বকেয়া দৈনিক ভাড়া নেই।'
                    );
                }

                $minimumDays = $outstandingDays >= 2 ? 2 : 1;

                if (
                    $requestedDays < $minimumDays ||
                    $requestedDays > $outstandingDays
                ) {
                    throw new \RuntimeException(
                        "আপনি {$minimumDays} থেকে {$outstandingDays} দিনের বকেয়া ভাড়া পরিশোধ করতে পারবেন।"
                    );
                }

                // FIFO: সবচেয়ে পুরোনো বকেয়া দিনগুলো আগে নেওয়া হবে।
                $selectedLedgers = $outstandingLedgers
                    ->take($requestedDays);

                $totalCents = 0;

                foreach ($selectedLedgers as $ledger) {
                    $totalCents += $ledger->payment_due_cents;
                }

                if ($totalCents <= 0) {
                    throw new \RuntimeException(
                        'পেমেন্টের পরিমাণ সঠিক নয়।'
                    );
                }

                $invoice = 'DR-' . now('Asia/Dhaka')->format('YmdHis')
                    . '-' . Str::upper(Str::random(6));

                $newPayment = Payment::query()->create([
                    'payment_no' => 'PAY-' . now('Asia/Dhaka')->format('YmdHis')
                        . '-' . Str::upper(Str::random(6)),
                    'applicant_id' => $lockedAllocation->applicant_id,
                    'allocation_id' => $lockedAllocation->id,
                    'payment_for' => 'daily_rent',
                    'gateway' => 'janata_pay',
                    'merchant_invoice_id' => $invoice,
                    'amount' => number_format($totalCents / 100, 2, '.', ''),
                    'currency' => 'BDT',
                    'status' => 'initiated',
                    'remarks' => 'Daily rent payment initiated through public portal.',
                ]);

                // প্রতিটি নির্বাচিত দিনের সম্পূর্ণ অবশিষ্ট টাকা আলাদাভাবে বরাদ্দ।
                foreach ($selectedLedgers as $ledger) {
                    $ledger->paymentAllocations()->create([
                        'payment_id' => $newPayment->id,
                        'amount' => number_format(
                            $ledger->payment_due_cents / 100,
                            2,
                            '.',
                            ''
                        ),
                        'remarks' => 'Daily rent payment allocation.',
                    ]);
                }

                return $newPayment;
            }, 5);

            // ডেটাবেজ Transaction শেষ হওয়ার পর JanataPay API কল করছি।
            $gatewayResponse = $janataPay->tokenize(
                Money::cents($payment->amount),
                'Daily Rent - Plot ' . $plotNumber,
                $payment->merchant_invoice_id
            );

            $payment->update([
                'transaction_token' => $gatewayResponse['transactionToken'],
                'gateway_url' => $gatewayResponse['url'],
                // 'status' => 'pending',
                'gateway_response' => $gatewayResponse,
            ]);

            return redirect()->away($payment->gateway_url);

        } catch (Throwable $exception) {
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'remarks' => 'JanataPay initiation failed: ' . $exception->getMessage(),
                ]);
            }

            report($exception);

            return back()->withErrors([
                'payment' => $exception instanceof \RuntimeException
                    ? $exception->getMessage()
                    : 'পেমেন্ট শুরু করা যায়নি। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।',
            ])->withInput();
        }
    }

}
