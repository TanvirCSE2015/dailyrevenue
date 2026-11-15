<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Plot;
use Illuminate\Http\Request;

class PlotApplicationController extends Controller
{
     public function create()
    {
        $plots = Plot::query()
            ->where('status', 'available')
            ->orderBy('plot_number')
            ->get([
                'id',
                'plot_number',
                'name',
                'location',
                'length',
                'width',
            ]);

        return view('frontend.pages.new-application', compact('plots'));
    }
}
