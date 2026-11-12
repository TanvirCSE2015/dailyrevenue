<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Applicant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'applicant_no',
        'name',
        'father_name',
        'mother_name',
        'present_address',
        'permanent_address',
        'mobile',
        'nid',
        'image',
        'nid_front',
        'nid_back',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(PlotApplication::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PlotAllocation::class);
    }

    public function activeAllocation(): HasOne
    {
        return $this->hasOne(PlotAllocation::class)
            ->where('status', 'active');
    }


    protected static function booted(): void
    {
        static::creating(function (Applicant $applicant) {
            if (blank($applicant->applicant_no)) {
                $applicant->applicant_no = static::generateApplicantNumber();
            }

            if (blank($applicant->created_by)) {
                $applicant->created_by = auth()->id();
            }
        });
    }


    public static function generateApplicantNumber(): string
    {
        return DB::transaction(function () {
            $yearMonth = now('Asia/Dhaka')->format('Ym');

            DB::table('applicant_number_sequences')->insertOrIgnore([
                'year_month' => $yearMonth,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = ApplicantNumberSequence::query()
                ->where('year_month', $yearMonth)
                ->lockForUpdate()
                ->firstOrFail();

            $nextNumber = $sequence->last_number + 1;

            $sequence->update([
                'last_number' => $nextNumber,
            ]);

            return 'APP-' . $yearMonth . '-' . str_pad(
                (string) $nextNumber,
                3,
                '0',
                STR_PAD_LEFT
            );
        }, 5);
    }
}
