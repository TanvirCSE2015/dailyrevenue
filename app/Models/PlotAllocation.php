<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlotAllocation extends Model
{
    protected $fillable = [
        'application_id',
        'applicant_id',
        'plot_id',
        'active_applicant_id',
        'active_plot_id',
        'allocated_by',
        'allocated_at',
        'start_date',
        'end_date',
        'last_synced_date',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'allocated_at' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'last_synced_date' => 'date',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(
            PlotApplication::class,
            'application_id'
        );
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function allocator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function dailyAttendances(): HasMany
    {
        return $this->hasMany(
            DailyAttendance::class,
            'allocation_id'
        );
    }

    public function dailyRentLedgers(): HasMany
    {
        return $this->hasMany(
            DailyRentLedger::class,
            'allocation_id'
        );
    }
}
