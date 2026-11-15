<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlotApplicationNumberSequence extends Model
{
     protected $fillable = [
        'year_month',
        'last_number',
    ];
}
