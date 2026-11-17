<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Payment;
use App\Models\Plot;
use App\Models\PlotApplication;
use App\Services\JanataPayClient;
use App\Services\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

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

   public function store(
        Request $request,
        JanataPayClient $janataPay
    ) {
        $storedFiles = [];
        $recordsCreated = false;
        $payment = null;

        try {
            /*
            |--------------------------------------------------------------------------
            | 1. Common Validation
            |--------------------------------------------------------------------------
            */

            $validated = $request->validate([
                'applicant_type' => [
                    'required',
                    'in:new,existing',
                ],

                'plot_id' => [
                    'required',
                    'exists:plots,id',
                ],

                'fee_agreement' => [
                    'required',
                    'accepted',
                ],

                'applicant_note' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | 2. Applicant Validation
            |--------------------------------------------------------------------------
            */

            if ($validated['applicant_type'] === 'new') {

                $applicantData = $request->validate([
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'father_name' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'mother_name' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    'mobile' => [
                        'required',
                        'string',
                        'max:20',
                        'unique:applicants,mobile',
                    ],

                    'nid' => [
                        'required',
                        'string',
                        'max:30',
                        'unique:applicants,nid',
                    ],

                    'present_address' => [
                        'required',
                        'string',
                    ],

                    'permanent_address' => [
                        'required',
                        'string',
                    ],

                    'image' => [
                        'required',
                        'image',
                        'mimes:jpg,jpeg,png,webp',
                        'max:2048',
                    ],

                    'nid_front' => [
                        'required',
                        'image',
                        'mimes:jpg,jpeg,png,webp',
                        'max:4096',
                    ],

                    'nid_back' => [
                        'required',
                        'image',
                        'mimes:jpg,jpeg,png,webp',
                        'max:4096',
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | 3. Store Applicant Documents
                |--------------------------------------------------------------------------
                */

                $applicantData['image'] = $request->file('image')
                    ->store('applicants/images', 'public');

                $applicantData['nid_front'] = $request->file('nid_front')
                    ->store('applicants/nid/front', 'public');

                $applicantData['nid_back'] = $request->file('nid_back')
                    ->store('applicants/nid/back', 'public');

                $storedFiles = [
                    $applicantData['image'],
                    $applicantData['nid_front'],
                    $applicantData['nid_back'],
                ];

                if (in_array(false, $storedFiles, true)) {
                    throw new RuntimeException(
                        'Applicant document upload failed.'
                    );
                }

            } else {

                /*
                |--------------------------------------------------------------------------
                | Existing Applicant Validation
                |--------------------------------------------------------------------------
                */

                $applicantData = $request->validate([
                    'applicant_no' => [
                        'required',
                        'string',
                        'max:50',
                    ],

                    'existing_mobile' => [
                        'required',
                        'string',
                        'max:20',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Create Application and Payment Records
            |--------------------------------------------------------------------------
            */

            [$applicant, $application, $payment] = DB::transaction(
                function () use ($validated, $applicantData) {

                    // Lock plot to prevent simultaneous processing.
                    $plot = Plot::query()
                        ->whereKey($validated['plot_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($plot->status !== 'available') {
                        throw ValidationException::withMessages([
                            'plot_id' => 'নির্বাচিত প্লটটি বর্তমানে আর খালি নেই।',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Find or Create Applicant
                    |--------------------------------------------------------------------------
                    */

                    if ($validated['applicant_type'] === 'new') {

                        $applicant = Applicant::create([
                            'name' => $applicantData['name'],
                            'father_name' => $applicantData['father_name'],
                            'mother_name' => $applicantData['mother_name'],
                            'mobile' => $applicantData['mobile'],
                            'nid' => $applicantData['nid'],
                            'present_address' => $applicantData['present_address'],
                            'permanent_address' => $applicantData['permanent_address'],
                            'image' => $applicantData['image'],
                            'nid_front' => $applicantData['nid_front'],
                            'nid_back' => $applicantData['nid_back'],
                            'is_active' => true,
                        ]);

                    } else {

                        $applicant = Applicant::query()
                            ->where(
                                'applicant_no',
                                $applicantData['applicant_no']
                            )
                            ->where(
                                'mobile',
                                $applicantData['existing_mobile']
                            )
                            ->where('is_active', true)
                            ->first();

                        if (! $applicant) {
                            throw ValidationException::withMessages([
                                'applicant_no' => 'আবেদনকারী নম্বর অথবা মোবাইল নম্বর সঠিক নয়।',
                            ]);
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Create Plot Application
                    |--------------------------------------------------------------------------
                    */

                    $application = PlotApplication::create([
                        'applicant_id' => $applicant->id,
                        'plot_id' => $plot->id,
                        'applied_at' => now('Asia/Dhaka')->toDateString(),
                        'application_fee_amount' => 100,
                        'status' => 'pending_payment',
                        'applicant_note' => $validated['applicant_note'] ?? null,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Generate Payment References
                    |--------------------------------------------------------------------------
                    */

                    $paymentNo = 'PAY-'
                        . now('Asia/Dhaka')->format('Ymd')
                        . '-'
                        . Str::upper(Str::random(8));

                    $invoiceId = 'PLOT-'
                        . now('Asia/Dhaka')->format('Ymd')
                        . '-'
                        . Str::upper(Str::random(8));

                    /*
                    |--------------------------------------------------------------------------
                    | Create Payment Record
                    |--------------------------------------------------------------------------
                    */

                    $payment = Payment::create([
                        'payment_no' => $paymentNo,
                        'applicant_id' => $applicant->id,
                        'application_id' => $application->id,
                        'payment_for' => 'application_fee',
                        'gateway' => 'janata_pay',
                        'merchant_invoice_id' => $invoiceId,
                        'amount' => 100,
                        'currency' => 'BDT',
                        'status' => 'initiated',
                    ]);

                    return [
                        $applicant,
                        $application,
                        $payment,
                    ];
                },
                5
            );

            $recordsCreated = true;

            /*
            |--------------------------------------------------------------------------
            | 5. Initiate JanataPay Hosted Payment
            |--------------------------------------------------------------------------
            */

            $gatewayResponse = $janataPay->tokenize(
                Money::cents($payment->amount),
                'Plot Application Fee - ' . $application->application_no,
                $payment->merchant_invoice_id
            );

            $transactionToken = $gatewayResponse['transactionToken'] ?? null;
            $gatewayUrl = $gatewayResponse['url'] ?? null;

            if (blank($transactionToken) || blank($gatewayUrl)) {
                throw new RuntimeException(
                    'JanataPay did not return a valid payment token or URL.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 6. Save Gateway Response
            |--------------------------------------------------------------------------
            */

            $payment->update([
                'transaction_token' => $transactionToken,
                'gateway_url' => $gatewayUrl,
                'status' => 'pending',
                'gateway_response' => $gatewayResponse,
            ]);

            /*
             * Application remains unsubmitted until payment
             * is successfully verified through JanataPay.
             */

            $application->update([
                'status' => 'pending_payment',
            ]);

            /*
            |--------------------------------------------------------------------------
            | 7. Redirect to JanataPay
            |--------------------------------------------------------------------------
            */

            return redirect()->away($gatewayUrl);

        } catch (ValidationException $exception) {

            // Remove uploaded documents if database transaction failed.
            if (! $recordsCreated && ! empty($storedFiles)) {
                Storage::disk('public')->delete($storedFiles);
            }

            throw $exception;

        } catch (Throwable $exception) {

            /*
            |--------------------------------------------------------------------------
            | 8. Handle Payment Initiation Failure
            |--------------------------------------------------------------------------
            */

            if (! $recordsCreated && ! empty($storedFiles)) {
                Storage::disk('public')->delete($storedFiles);
            }

            if ($recordsCreated && $payment) {

                $payment->update([
                    'status' => 'failed',
                    'remarks' => 'JanataPay payment initiation failed.',
                    'gateway_response' => [
                        'error' => $exception->getMessage(),
                    ],
                ]);
            }

            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'payment' => 'পেমেন্ট চালু করা যায়নি। অনুগ্রহ করে আবার চেষ্টা করুন।',
                ]);
        }
    }
}
