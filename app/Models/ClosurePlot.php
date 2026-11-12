<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClosurePlot extends Model
{
    protected $fillable = [
        'office_closure_id',
        'plot_id',
    ];

    public function officeClosure(): BelongsTo
    {
        return $this->belongsTo(OfficeClosure::class);
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }
}
