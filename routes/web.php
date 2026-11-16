<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Frontend\PlotApplicationController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::view('/abedoner-niyomaboli', 'frontend.pages.rules')
    ->name('frontend.rules');

Route::get('/new-application', [PlotApplicationController::class, 'create'])
    ->name('frontend.applications.create');

Route::prefix('payment')->name('frontend.payment.')->group(function () {

    Route::get('/success', [PaymentController::class, 'success'])
        ->name('success');

    Route::get('/failure', [PaymentController::class, 'failure'])
        ->name('failure');

    Route::get('/cancel', [PaymentController::class, 'cancel'])
        ->name('cancel');

});
