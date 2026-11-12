<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenceNotice extends Model
{
    protected $fillable = [
        'notice_no',
        'allocation_id',
        'consecutive_absent_days',
        'absence_start_date',
        'notice_date',
        'response_deadline',
        'status',
        'notice_content',
        'applicant_response',
        'responded_at',
        'issued_by',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'absence_start_date' => 'date',
            'notice_date' => 'date',
            'response_deadline' => 'date',
            'responded_at' => 'datetime',
        ];
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(PlotAllocation::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
