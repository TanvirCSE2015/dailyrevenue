<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'refund_no',
        'allocation_id',
        'cancellation_id',
        'payment_id',
        'refund_type',
        'refund_method',
        'amount',
        'transaction_reference',
        'status',
        'refund_date',
        'requested_by',
        'approved_by',
        'approved_at',
        'processed_by',
        'processed_at',
        'reason',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'refund_date' => 'date',
            'approved_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(PlotAllocation::class);
    }

    public function cancellation(): BelongsTo
    {
        return $this->belongsTo(
            AllocationCancellation::class,
            'cancellation_id'
        );
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
