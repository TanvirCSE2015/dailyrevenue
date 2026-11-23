<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\DailyRentLedger;
use App\Models\Payment;
use App\Models\PlotAllocation;
use Illuminate\Http\Request;

class ApplicantSearchController extends Controller
{
     /**
     * Search applicant by applicant number, mobile or NID.
     */
    
       public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'max:30'],
        ], [
            'q.required' => 'আবেদনকারী নম্বর লিখুন।',
            'q.max' => 'আবেদনকারী নম্বর সর্বোচ্চ ৩০ অক্ষরের হতে পারবে।',
        ]);

        $search = str_replace(
            ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            trim($validated['q'])
        );

        $applicant = Applicant::query()
            ->where('applicant_no', $search)
            ->where('is_active', true)
            ->first();

        $isVerified = false;

        if (
            $applicant &&
            session('verified_applicant_id') == $applicant->id &&
            session('applicant_verified_at')
        ) {
            $verifiedAt = \Illuminate\Support\Carbon::parse(
                session('applicant_verified_at')
            );

            if ($verifiedAt->addMinutes(30)->isFuture()) {
                $isVerified = true;
            } else {
                $request->session()->forget([
                    'verified_applicant_id',
                    'applicant_verified_at',
                ]);
            }
        }

        if (
            session('verified_applicant_id') &&
            $applicant &&
            session('verified_applicant_id') != $applicant->id
        ) {
            $request->session()->forget([
                'verified_applicant_id',
                'applicant_verified_at',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Payment and Due Information
        |--------------------------------------------------------------------------
        */

        $paymentRows = collect();
        $dueMonths = collect();

        $paymentTotal = 0;
        $dueTotal = 0;

        $approvedApplication = null;
        $securityDepositPayment = null;

        if ($applicant && $isVerified) {

            // Applicant's all current and previous allocations
            $allocationIds = PlotAllocation::query()
                ->where('applicant_id', $applicant->id)
                ->pluck('id');

            // All rent ledgers belonging to those allocations
            $ledgers = DailyRentLedger::query()
                ->with([
                    'allocation.plot',
                    'paymentAllocations.payment',
                ])
                ->whereIn('allocation_id', $allocationIds)
                ->orderBy('rent_date')
                ->get();

            $paymentRowsByKey = [];
            $dueMonthsByKey = [];

            foreach ($ledgers as $ledger) {

                $monthKey = $ledger->rent_date->format('Y-m');

                $monthLabel = $ledger->rent_date->format('m/Y');

                $plotNumber = $ledger->allocation?->plot?->plot_number
                    ?? '—';

                /*
                |--------------------------------------------------------------------------
                | Calculate Successfully Paid Amount
                |--------------------------------------------------------------------------
                */

                $paidAmount = 0;

                foreach ($ledger->paymentAllocations as $paymentAllocation) {

                    $payment = $paymentAllocation->payment;

                    if (
                        ! $payment ||
                        $payment->status !== 'success' ||
                        ! $payment->verified_at ||
                        $payment->payment_for !== 'daily_rent'
                    ) {
                        continue;
                    }

                    $amount = (float) $paymentAllocation->amount;

                    $paidAmount += $amount;

                    /*
                    |--------------------------------------------------------------------------
                    | Group Payment Transactions by Payment and Rent Month
                    |--------------------------------------------------------------------------
                    */

                    $paymentKey = $payment->id . '_' . $monthKey;

                    if (! isset($paymentRowsByKey[$paymentKey])) {

                        $paymentRowsByKey[$paymentKey] = [
                            'month_key' => $monthKey,
                            'month' => $monthLabel,
                            'payment_no' => $payment->payment_no,
                            'paid_at' => $payment->paid_at,
                            'gateway' => $payment->gateway,
                            'amount' => 0,
                            'plots' => [],
                        ];
                    }

                    $paymentRowsByKey[$paymentKey]['amount'] += $amount;

                    if (
                        ! in_array(
                            $plotNumber,
                            $paymentRowsByKey[$paymentKey]['plots'],
                            true
                        )
                    ) {
                        $paymentRowsByKey[$paymentKey]['plots'][] = $plotNumber;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Calculate Daily Due Amount
                |--------------------------------------------------------------------------
                */

                $netAmount = (float) $ledger->net_amount;

                $dueAmount = max(0, $netAmount - $paidAmount);

                /*
                |--------------------------------------------------------------------------
                | Group Ledger Information by Month
                |--------------------------------------------------------------------------
                */

                if (! isset($dueMonthsByKey[$monthKey])) {

                    $dueMonthsByKey[$monthKey] = [
                        'month_key' => $monthKey,
                        'month' => $monthLabel,
                        'base_amount' => 0,
                        'adjustment_amount' => 0,
                        'net_amount' => 0,
                        'paid_amount' => 0,
                        'due_amount' => 0,
                        'days' => 0,
                        'plots' => [],
                    ];
                }

                $dueMonthsByKey[$monthKey]['base_amount'] +=
                    (float) $ledger->base_amount;

                $dueMonthsByKey[$monthKey]['adjustment_amount'] +=
                    (float) $ledger->adjustment_amount;

                $dueMonthsByKey[$monthKey]['net_amount'] += $netAmount;

                $dueMonthsByKey[$monthKey]['paid_amount'] += $paidAmount;

                $dueMonthsByKey[$monthKey]['due_amount'] += $dueAmount;

                $dueMonthsByKey[$monthKey]['days']++;

                if (
                    ! in_array(
                        $plotNumber,
                        $dueMonthsByKey[$monthKey]['plots'],
                        true
                    )
                ) {
                    $dueMonthsByKey[$monthKey]['plots'][] = $plotNumber;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Final Payment Collection
            |--------------------------------------------------------------------------
            */

            $paymentRows = collect(array_values($paymentRowsByKey))
                ->sortByDesc(function ($row) {
                    return $row['paid_at']?->timestamp ?? 0;
                })
                ->values();

            $paymentTotal = $paymentRows->sum('amount');

            /*
            |--------------------------------------------------------------------------
            | Final Monthly Due Collection
            |--------------------------------------------------------------------------
            */

            $dueMonths = collect(array_values($dueMonthsByKey))
                ->sortByDesc('month_key')
                ->values();

            $dueTotal = $dueMonths->sum('due_amount');
        }

        if ($applicant && $isVerified) {

            // Get latest approved application.
            $approvedApplication = \App\Models\PlotApplication::query()
                ->where('applicant_id', $applicant->id)
                ->where('status', 'approved')
                ->latest('id')
                ->first();

            if ($approvedApplication) {

                // Get latest security deposit payment.
                $securityDepositPayment = Payment::query()
                    ->where('application_id', $approvedApplication->id)
                    ->where('applicant_id', $applicant->id)
                    ->where('payment_for', 'security_deposit')
                    ->latest('id')
                    ->first();
            }
        }

        return view('frontend.applicants.search', [
            'applicant' => $applicant,
            'isVerified' => $isVerified,
            'search' => $search,

            'paymentRows' => $paymentRows,
            'paymentTotal' => $paymentTotal,

            'dueMonths' => $dueMonths,
            'dueTotal' => $dueTotal,
            'approvedApplication'=>$approvedApplication, 
            'securityDepositPayment' => $securityDepositPayment,
        ]);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'applicant_no' => ['required', 'string', 'max:30'],
            'nid' => ['required', 'string', 'max:30'],
            'mobile' => ['required', 'string', 'max:20'],
        ], [
            'applicant_no.required' => 'আবেদনকারী নম্বর পাওয়া যায়নি।',
            'nid.required' => 'জাতীয় পরিচয়পত্র নম্বর লিখুন।',
            'mobile.required' => 'মোবাইল নম্বর লিখুন।',
        ]);

        $toEnglishNumber = function ($value) {
            return str_replace(
                ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'],
                ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                trim($value)
            );
        };

        $applicantNo = trim($validated['applicant_no']);
        $nid = $toEnglishNumber($validated['nid']);
        $mobile = $toEnglishNumber($validated['mobile']);

        $applicant = Applicant::query()
            ->where('applicant_no', $applicantNo)
            ->where('nid', $nid)
            ->where('mobile', $mobile)
            ->where('is_active', true)
            ->first();

        if (! $applicant) {
            return response()->json([
                'success' => false,
                'message' => 'আপনার দেওয়া NID অথবা মোবাইল নম্বর সঠিক নয়। অনুগ্রহ করে পুনরায় চেষ্টা করুন।',
            ], 422);
        }

        $request->session()->regenerate();

        $request->session()->put([
            'verified_applicant_id' => $applicant->id,
            'applicant_verified_at' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'আপনার পরিচয় সফলভাবে যাচাই হয়েছে।',
            'applicant_id' => $applicant->id,
        ]);
    }


}
