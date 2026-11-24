<?php

namespace App\Services;

use App\Models\Applicant;
use App\Models\DailyRentLedger;
use App\Models\DepositTransaction;
use App\Models\Payment;

class ApplicantFinancialSummaryService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }


        public function calculate(Applicant $applicant): array
    {
        /*
        |--------------------------------------------------------------------------
        | ১. দৈনিক ভাড়ার হিসাব
        |--------------------------------------------------------------------------
        */

        $ledgers = DailyRentLedger::query()
            ->whereHas('allocation', function ($query) use ($applicant) {
                $query->where('applicant_id', $applicant->id);
            })
            ->with([
                'adjustments' => fn ($query) =>
                    $query->where('status', 'approved'),

                'paymentAllocations' => fn ($query) =>
                    $query->whereHas('payment', function ($paymentQuery) {
                        $paymentQuery
                            ->where('status', 'success')
                            ->whereNotNull('verified_at');
                    }),
            ])
            ->get();

        $totalRent = 0;
        $totalWaived = 0;
        $totalPaidRent = 0;

        foreach ($ledgers as $ledger) {
            $baseAmount = $this->toCents($ledger->base_amount);

            $waivedAmount = $ledger->adjustments->sum(
                fn ($adjustment) =>
                    $this->toCents($adjustment->approved_amount)
            );

            // একটি দিনের মূল ভাড়ার বেশি মওকুফ গণনা হবে না।
            $waivedAmount = min($baseAmount, $waivedAmount);

            $paidAmount = $ledger->paymentAllocations->sum(
                fn ($allocation) =>
                    $this->toCents($allocation->amount)
            );

            $totalRent += $baseAmount;
            $totalWaived += $waivedAmount;
            $totalPaidRent += $paidAmount;
        }

        $totalPayableRent = max(
            0,
            $totalRent - $totalWaived
        );

        $totalDueRent = max(
            0,
            $totalPayableRent - $totalPaidRent
        );

        /*
        |--------------------------------------------------------------------------
        | ২. আবেদন ফি
        |--------------------------------------------------------------------------
        */

        $applicationFee = Payment::query()
            ->where('applicant_id', $applicant->id)
            ->where('payment_for', 'application_fee')
            ->where('status', 'success')
            ->whereNotNull('verified_at')
            ->get(['amount'])
            ->sum(fn ($payment) =>
                $this->toCents($payment->amount)
            );

        /*
        |--------------------------------------------------------------------------
        | ৩. জামানতের Transaction
        |--------------------------------------------------------------------------
        |
        | Manual transaction হলে payment_id NULL থাকতে পারে।
        | Payment-linked transaction হলে Payment সফল ও যাচাইকৃত
        | হতে হবে।
        |
        */

        $depositTransactions = DepositTransaction::query()
            ->whereHas('allocation', function ($query) use ($applicant) {
                $query->where('applicant_id', $applicant->id);
            })
            ->where(function ($query) {
                $query
                    ->whereNull('payment_id')
                    ->orWhereHas('payment', function ($paymentQuery) {
                        $paymentQuery
                            ->where('status', 'success')
                            ->whereNotNull('verified_at');
                    });
            })
            ->get([
                'transaction_type',
                'amount',
            ]);

        $deposit = 0;
        $deduction = 0;
        $refund = 0;
        $adjustmentCredit = 0;
        $adjustmentDebit = 0;

        foreach ($depositTransactions as $transaction) {
            $amount = $this->toCents($transaction->amount);

            match ($transaction->transaction_type) {
                'deposit' => $deposit += $amount,

                'deduction' => $deduction += $amount,

                'refund' => $refund += $amount,

                'adjustment_credit' => $adjustmentCredit += $amount,

                'adjustment_debit' => $adjustmentDebit += $amount,

                default => null,
            };
        }

        $depositBalance = max(
            0,
            $deposit
                + $adjustmentCredit
                - $deduction
                - $refund
                - $adjustmentDebit
        );

        /*
        |--------------------------------------------------------------------------
        | ৪. সম্পূর্ণ Financial Summary
        |--------------------------------------------------------------------------
        */

        return [
            'daily_rent' => [
                'total_rent' => $this->toMoney($totalRent),
                'waived_amount' => $this->toMoney($totalWaived),
                'payable_amount' => $this->toMoney($totalPayableRent),
                'paid_amount' => $this->toMoney($totalPaidRent),
                'due_amount' => $this->toMoney($totalDueRent),
            ],

            'application' => [
                'application_fee_paid' => $this->toMoney(
                    $applicationFee
                ),
            ],

            'security_deposit' => [
                'total_deposit' => $this->toMoney($deposit),
                'total_deduction' => $this->toMoney($deduction),
                'total_refund' => $this->toMoney($refund),
                'adjustment_credit' => $this->toMoney($adjustmentCredit),
                'adjustment_debit' => $this->toMoney($adjustmentDebit),
                'current_balance' => $this->toMoney($depositBalance),
            ],
        ];
    }

    /**
     * টাকা পয়সাকে পূর্ণ সংখ্যক পয়সায় রূপান্তর।
     */
    private function toCents(mixed $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    /**
     * পয়সাকে দুই দশমিকের টাকার পরিমাণে রূপান্তর।
     */
    private function toMoney(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
