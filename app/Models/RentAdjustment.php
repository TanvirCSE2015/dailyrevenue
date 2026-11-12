<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentAdjustment extends Model
{
    protected $fillable = [
        'daily_rent_ledger_id',
        'adjustment_type',
        'adjustment_mode',
        'requested_amount',
        'approved_amount',
        'status',
        'reason',
        'attachment_path',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_remarks',
    ];

    protected function casts(): array
    {
        return [
            'requested_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function dailyRentLedger(): BelongsTo
    {
        return $this->belongsTo(
            DailyRentLedger::class,
            'daily_rent_ledger_id'
        );
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
