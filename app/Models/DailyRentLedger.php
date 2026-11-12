<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DailyRentLedger extends Model
{
    protected $fillable = [
        'allocation_id',
        'attendance_id',
        'rate_history_id',
        'rent_date',
        'base_amount',
        'adjustment_amount',
        'net_amount',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'rent_date' => 'date',
            'base_amount' => 'decimal:2',
            'adjustment_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
        ];
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(PlotAllocation::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(
            DailyAttendance::class,
            'attendance_id'
        );
    }

    public function rateHistory(): BelongsTo
    {
        return $this->belongsTo(RateHistory::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(
            RentAdjustment::class,
            'daily_rent_ledger_id'
        );
    }

    public function paymentAllocations(): MorphMany
    {
        return $this->morphMany(
            PaymentAllocation::class,
            'payable'
        );
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->paymentAllocations()
            ->whereHas('payment', function ($query) {
                $query->where('status', 'success');
            })
            ->sum('amount');
    }

    public function getDueAmountAttribute(): float
    {
        return max(
            0,
            (float) $this->net_amount - $this->paid_amount
        );
    }
}
