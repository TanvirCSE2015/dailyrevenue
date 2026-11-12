<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AllocationCancellation extends Model
{
     protected $fillable = [
        'cancellation_no',
        'allocation_id',
        'absence_notice_id',
        'cancellation_type',
        'requested_date',
        'effective_date',
        'status',
        'reason',
        'applicant_statement',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_remarks',
        'completed_at',
        'completed_by',
        'completion_remarks',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'effective_date' => 'date',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(PlotAllocation::class);
    }

    public function absenceNotice(): BelongsTo
    {
        return $this->belongsTo(AbsenceNotice::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'cancellation_id');
    }
}
