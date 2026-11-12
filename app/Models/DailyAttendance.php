<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DailyAttendance extends Model
{
     protected $fillable = [
        'allocation_id',
        'attendance_date',
        'attendance_status',
        'marked_by',
        'marked_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'marked_at' => 'datetime',
        ];
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(PlotAllocation::class);
    }

    public function marker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function dailyRentLedger(): HasOne
    {
        return $this->hasOne(
            DailyRentLedger::class,
            'attendance_id'
        );
    }
}
