<?php

namespace App\Services;

use App\Models\RateHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class RateHistoryService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

     public function createRate(
        string $rateType,
        float $amount,
        string $effectiveFrom,
        ?string $description = null,
        ?int $createdBy = null
    ): RateHistory {
        $allowedTypes = [
            'application_fee',
            'security_deposit',
            'daily_rent',
        ];

        if (!in_array($rateType, $allowedTypes, true)) {
            throw new InvalidArgumentException(
                'Invalid rate type.'
            );
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException(
                'Rate amount must be greater than zero.'
            );
        }

        $startDate = Carbon::parse($effectiveFrom)->startOfDay();

        return DB::transaction(function () use (
            $rateType,
            $amount,
            $startDate,
            $description,
            $createdBy
        ) {
            $rates = RateHistory::query()
                ->where('rate_type', $rateType)
                ->orderByDesc('effective_from')
                ->lockForUpdate()
                ->get();

            $latestRate = $rates->first();

            if (
                $latestRate &&
                $startDate->lte(
                    Carbon::parse($latestRate->effective_from)
                        ->startOfDay()
                )
            ) {
                throw new RuntimeException(
                    'New rate effective date must be later than the latest rate date.'
                );
            }

            // নতুন Rate-এর আগের দিন পর্যন্ত
            // পূর্ববর্তী Rate কার্যকর থাকবে।
            if ($latestRate && !$latestRate->effective_until) {
                $latestRate->effective_until = $startDate
                    ->copy()
                    ->subDay()
                    ->toDateString();

                $latestRate->save();
            } elseif (
                $latestRate &&
                Carbon::parse($latestRate->effective_until)
                    ->gte($startDate)
            ) {
                throw new RuntimeException(
                    'The new rate overlaps with an existing rate.'
                );
            }

            return RateHistory::create([
                'rate_type' => $rateType,
                'amount' => $amount,
                'effective_from' => $startDate->toDateString(),
                'effective_until' => null,
                'description' => $description,
                'created_by' => $createdBy,
            ]);
        }, 3);
    }
}
