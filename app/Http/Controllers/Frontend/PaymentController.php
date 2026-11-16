<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function success(Request $request)
    {
        return view('frontend.pages.payment.success');
    }

    /**
     * Payment Failed
     */
    public function failure(Request $request)
    {
        return view('frontend.pages.payment.failure');
    }

    /**
     * Payment Cancelled
     */
    public function cancel(Request $request)
    {
        return view('frontend.pages.payment.cancel');
    }
}
