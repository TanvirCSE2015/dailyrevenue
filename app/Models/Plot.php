<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plot extends Model
{
     use SoftDeletes;

    protected $fillable = [
        'plot_number',
        'name',
        'location',
        'length',
        'width',
        'status',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'length' => 'decimal:2',
            'width' => 'decimal:2',
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

    public function closurePlots(): HasMany
    {
        return $this->hasMany(ClosurePlot::class);
    }
}
