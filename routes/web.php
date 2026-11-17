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

Route::prefix('application')->name('frontend.applications.')->group(function () {
    Route::get('/new', [PlotApplicationController::class, 'create'])
        ->name('create');

    Route::post('/store', [PlotApplicationController::class, 'store'])
        ->name('store');
});

Route::prefix('payments')->name('frontend.payment.')->group(function () {

    Route::get('/success', [PaymentController::class, 'success'])
        ->name('success');

    Route::get('/fail', [PaymentController::class, 'failure'])
        ->name('failure');

    Route::get('/cancel', [PaymentController::class, 'cancel'])
        ->name('cancel');

});
