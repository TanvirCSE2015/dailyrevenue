<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OfficeClosure extends Model
{
     protected $fillable = [
        'closure_no',
        'title',
        'closure_type',
        'start_date',
        'end_date',
        'is_global',
        'reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_global' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closurePlots(): HasMany
    {
        return $this->hasMany(ClosurePlot::class);
    }
}
