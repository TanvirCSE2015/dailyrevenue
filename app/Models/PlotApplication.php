<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PlotApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_no',
        'applicant_id',
        'plot_id',
        'applied_at',
        'application_fee_amount',
        'status',
        'applicant_note',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'date',
            'application_fee_amount' => 'decimal:2',
        ];
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ApplicationReview::class, 'application_id');
    }

    public function allocation(): HasOne
    {
        return $this->hasOne(PlotAllocation::class, 'application_id');
    }

    public function absenceNotices(): HasMany
    {
        return $this->hasMany(AbsenceNotice::class, 'allocation_id');
    }

    public function cancellations(): HasMany
    {
        return $this->hasMany(
            AllocationCancellation::class,
            'allocation_id'
        );
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'allocation_id');
    }

    protected static function booted(): void
    {
        static::creating(function (PlotApplication $application) {
            if (blank($application->application_no)) {
                $application->application_no = static::generateApplicationNumber();
            }
        });
    }

    public static function generateApplicationNumber(): string
    {
        return DB::transaction(function () {

            $yearMonth = now('Asia/Dhaka')->format('Ym');

            DB::table('plot_application_number_sequences')->insertOrIgnore([
                'year_month' => $yearMonth,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = PlotApplicationNumberSequence::query()
                ->where('year_month', $yearMonth)
                ->lockForUpdate()
                ->firstOrFail();

            $nextNumber = $sequence->last_number + 1;

            $sequence->update([
                'last_number' => $nextNumber,
            ]);

            return 'PLA-' . $yearMonth . '-' . str_pad(
                (string) $nextNumber,
                4,
                '0',
                STR_PAD_LEFT
            );

        }, 5);
    }
}
