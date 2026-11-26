<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentAdjustmentBatch extends Model
{
    protected $fillable = [
        'applicant_id',
        'adjustment_type',
        'adjustment_basis',
        'adjustment_days',
        'requested_amount',
        'approved_amount',
        'reason',
        'attachment_path',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'status',
        'approval_remarks',
    ];

    protected function casts(): array
    {
        return [
            'adjustment_days' => 'integer',
            'requested_amount' => 'decimal:2',
            'approved_amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'requested_by'
        );
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(
            RentAdjustment::class,
            'batch_id'
        );
    }
}
