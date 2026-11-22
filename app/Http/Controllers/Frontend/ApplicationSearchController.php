<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PlotApplication;
use App\Services\JanataPayClient;
use App\Services\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ApplicationSearchController extends Controller
{
    /**
     * Search application by application number.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'application_no' => [
                'required',
                'string',
                'max:50',
            ],
        ], [
            'application_no.required' => 'আবেদন নম্বর লিখুন।',
        ]);

        $applicationNo = Str::upper(
            trim($validated['application_no'])
        );

        $application = PlotApplication::query()
            ->with([
                'applicant',
                'plot',
                'allocation',
            ])
            ->where('application_no', $applicationNo)
            ->first();

        $depositPaid = false;

        if ($application) {
            $depositPaid = Payment::query()
                ->where('application_id', $application->id)
                ->where('payment_for', 'security_deposit')
                ->where('status', 'success')
                ->whereNotNull('verified_at')
                ->exists();
        }

        return view(
            'frontend.pages.application-search-result',
            compact(
                'application',
                'applicationNo',
                'depositPaid'
            )
        );
    }

    /**
     * Initiate security deposit payment through JanataPay.
     */
    public function securityDeposit(
        Request $request,
        PlotApplication $application,
        JanataPayClient $janataPay
    ) {
        if ($application->status !== 'approved') {
            return back()->withErrors([
                'payment' => 'শুধুমাত্র অনুমোদিত আবেদনের জন্য সিকিউরিটি ডিপোজিট দেওয়া যাবে।',
            ]);
        }

        try {
            $result = DB::transaction(function () use ($application, $janataPay) {

                /*
                * Lock application to prevent concurrent
                * security deposit payment initiation.
                */
                $lockedApplication = PlotApplication::query()
                    ->whereKey($application->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedApplication->status !== 'approved') {
                    throw new \RuntimeException(
                        'আবেদনটি বর্তমানে অনুমোদিত অবস্থায় নেই।'
                    );
                }

                /*
                * Check whether deposit is already paid.
                */
                $depositPaid = Payment::query()
                    ->where('application_id', $lockedApplication->id)
                    ->where('payment_for', 'security_deposit')
                    ->where('status', 'success')
                    ->whereNotNull('verified_at')
                    ->exists();

                if ($depositPaid) {
                    throw new \RuntimeException(
                        'এই আবেদনের সিকিউরিটি ডিপোজিট ইতোমধ্যে পরিশোধ করা হয়েছে।'
                    );
                }

                /*
                * Find the latest active payment.
                */
                $existingPayment = Payment::query()
                    ->where('application_id', $lockedApplication->id)
                    ->where('payment_for', 'security_deposit')
                    ->where('gateway', 'janata_pay')
                    ->whereIn('status', ['initiated', 'pending'])
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();

                /*
                * Reuse an existing payment if its gateway
                * URL is already available.
                */
                if ($existingPayment && filled($existingPayment->gateway_url)) {
                    return [
                        'payment' => $existingPayment,
                        'gateway_url' => $existingPayment->gateway_url,
                        'error' => null,
                    ];
                }

                /*
                * An incomplete previous payment cannot be
                * reused. Mark it failed before retrying.
                */
                if ($existingPayment) {
                    $existingPayment->update([
                        'status' => 'failed',
                        'remarks' => 'Previous security deposit payment initiation was incomplete.',
                    ]);
                }

                /*
                * Create a fresh payment record.
                */
                $paymentNo = 'PAY-'
                    . now('Asia/Dhaka')->format('Ymd')
                    . '-'
                    . Str::upper(Str::random(8));

                $invoiceId = 'PLOT-'
                    . now('Asia/Dhaka')->format('Ymd')
                    . '-'
                    . Str::upper(Str::random(8));

                $payment = Payment::create([
                    'payment_no' => $paymentNo,
                    'applicant_id' => $lockedApplication->applicant_id,
                    'application_id' => $lockedApplication->id,
                    'payment_for' => 'security_deposit',
                    'gateway' => 'janata_pay',
                    'merchant_invoice_id' => $invoiceId,
                    'amount' => 1000,
                    'currency' => 'BDT',
                    'status' => 'initiated',
                    'remarks' => 'Security deposit payment.',
                ]);

                /*
                * Initiate JanataPay while holding the
                * application lock.
                */
                try {
                    $gatewayResponse = $janataPay->tokenize(
                        Money::cents($payment->amount),
                        'Security Deposit - ' . $lockedApplication->application_no,
                        $payment->merchant_invoice_id
                    );

                    $transactionToken = $gatewayResponse['transactionToken'] ?? null;
                    $gatewayUrl = $gatewayResponse['url'] ?? null;

                    if (blank($transactionToken) || blank($gatewayUrl)) {
                        throw new \RuntimeException(
                            'JanataPay did not return a valid payment token or URL.'
                        );
                    }

                    $payment->update([
                        'transaction_token' => $transactionToken,
                        'gateway_url' => $gatewayUrl,
                        'status' => 'pending',
                        'gateway_response' => $gatewayResponse,
                    ]);

                    return [
                        'payment' => $payment->fresh(),
                        'gateway_url' => $gatewayUrl,
                        'error' => null,
                    ];

                } catch (Throwable $gatewayException) {

                    /*
                    * Save failed initiation and allow retry.
                    */
                    $payment->update([
                        'status' => 'failed',
                        'remarks' => 'Security deposit payment initiation failed.',
                        'gateway_response' => [
                            'error' => $gatewayException->getMessage(),
                        ],
                    ]);

                    return [
                        'payment' => $payment->fresh(),
                        'gateway_url' => null,
                        'error' => $gatewayException->getMessage(),
                    ];
                }

            }, 5);

            /*
            * Handle gateway initiation failure.
            */
            if ($result['error']) {
                throw new \RuntimeException($result['error']);
            }

            /*
            * Redirect to JanataPay.
            */
            return redirect()->away($result['gateway_url']);

        } catch (Throwable $exception) {

            report($exception);

            return redirect()
                ->route('frontend.applications.search', [
                    'application_no' => $application->application_no,
                ])
                ->withErrors([
                    'payment' => $exception instanceof \RuntimeException
                        ? $exception->getMessage()
                        : 'সিকিউরিটি ডিপোজিট পেমেন্ট চালু করা যায়নি। অনুগ্রহ করে আবার চেষ্টা করুন।',
                ]);
        }
    }

}
