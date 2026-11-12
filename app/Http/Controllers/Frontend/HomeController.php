<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Plot;
use App\Models\PlotApplication;
use App\Models\PublicNotice;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $statistics = [
            'total_plots' => Plot::count(),

            'available_plots' => Plot::where(
                'status',
                'available'
            )->count(),

            'allocated_plots' => Plot::where(
                'status',
                'allocated'
            )->count(),

            'total_applications' => PlotApplication::count(),
        ];

        $notices = PublicNotice::published()
            ->latest('published_at')
            ->latest()
            ->take(6)
            ->get();

        return view('frontend.pages.home', compact(
            'statistics',
            'notices'
        ));
    }
}
