<?php

use App\Http\Controllers\Frontend\HomeController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::view('/abedoner-niyomaboli', 'frontend.pages.rules')
    ->name('frontend.rules');
