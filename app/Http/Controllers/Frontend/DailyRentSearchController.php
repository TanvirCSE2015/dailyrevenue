<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\DailyRentLedger;
use App\Models\Plot;
use App\Models\PlotAllocation;
use App\Services\DailyRentLedgerService;
use Illuminate\Http\Request;

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
}
