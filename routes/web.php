<?php

use App\Http\Controllers\Frontend\ApplicantSearchController;
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

Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success');
Route::get('/payments/fail', [PaymentController::class, 'failure'])->name('payments.fail');
Route::get('/payments/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');

Route::get('/applicant-search', [ApplicantSearchController::class, 'search'])
    ->name('frontend.applicants.search');

Route::post('/applicant-verify', [ApplicantSearchController::class, 'verify'])->name('frontend.applicants.verify');
