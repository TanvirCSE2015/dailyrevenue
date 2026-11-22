<?php

use App\Http\Controllers\Frontend\ApplicantSearchController;
use App\Http\Controllers\Frontend\ApplicationSearchController;
use App\Http\Controllers\Frontend\DailyRentSearchController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PaymentController;
use App\Http\Controllers\Frontend\PlotApplicationController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Home
Route::get('/', [HomeController::class, 'index'])
    ->name('home');

// Application Rules
Route::view('/abedoner-niyomaboli', 'frontend.pages.rules')
    ->name('frontend.rules');

// New Application
Route::prefix('application')->name('frontend.applications.')->group(function () {
    Route::get('/new', [PlotApplicationController::class, 'create'])
        ->name('create');

    Route::post('/store', [PlotApplicationController::class, 'store'])
        ->name('store');

    // Search application by application number
    Route::get('/search', [ApplicationSearchController::class, 'search'])
        ->name('search');
    Route::post('/{application}/security-deposit', [ApplicationSearchController::class, 'securityDeposit'])
        ->name('security-deposit');
});

// Daily Rent Search by Plot Number
Route::get('/daily-rent-search', [DailyRentSearchController::class, 'search'])
    ->name('frontend.daily-rent.search');

// Payment Gateway Callback
Route::get('/payments/success', [PaymentController::class, 'success'])
    ->name('payments.success');

Route::get('/payments/fail', [PaymentController::class, 'failure'])
    ->name('payments.fail');

Route::get('/payments/cancel', [PaymentController::class, 'cancel'])
    ->name('payments.cancel');

// Existing Applicant Search
Route::get('/applicant-search', [ApplicantSearchController::class, 'search'])
    ->name('frontend.applicants.search');

Route::post('/applicant-verify', [ApplicantSearchController::class, 'verify'])
    ->name('frontend.applicants.verify');
