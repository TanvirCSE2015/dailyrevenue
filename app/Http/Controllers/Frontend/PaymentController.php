<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Plot;
use App\Models\PlotAllocation;
use App\Models\PlotApplication;
use App\Services\GatewayException;
use App\Services\JanataPayClient;
use App\Services\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InvalidArgumentException;

class PaymentController extends Controller
{
   public function success(
        Request $request,
        JanataPayClient $gateway
    ): View {
        return $this->callback($request, 'success', $gateway);
    }

    /**
     * Payment failed callback.
     */
    public function failure(
        Request $request,
        JanataPayClient $gateway
    ): View {
        return $this->callback($request, 'failure', $gateway);
    }

    /**
     * Payment cancelled callback.
     */
    public function cancel(
        Request $request,
        JanataPayClient $gateway
    ): View {
        return $this->callback($request, 'cancel', $gateway);
    }

    /**
     * Common callback handler.
     */
    
    private function callback(
        Request $request,
        string $outcome,
        JanataPayClient $gateway
    ): View {
        $referenceId = trim(
            (string) $request->query('refid', '')
        );

        if ($referenceId === '') {
            abort(400, 'Missing payment reference.');
        }

        $payment = Payment::query()
            ->where('merchant_invoice_id', $referenceId)
            ->first();

        // Payment reference not found.
        if (! $payment) {
            abort(404, 'Payment record not found.');
        }

        Log::info('JanataPay Callback Received', [
            'payment_id' => $payment->id,
            'merchant_invoice_id' => $payment->merchant_invoice_id,
            'payment_for' => $payment->payment_for,
            'outcome' => $outcome,
        ]);

        try {
            $this->verifyAndSave($payment, $gateway);
        } catch (\Throwable $exception) {
            Log::error('JanataPay payment verification error.', [
                'payment_id' => $payment->id,
                'merchant_invoice_id' => $payment->merchant_invoice_id,
                'payment_for' => $payment->payment_for,
                'message' => $exception->getMessage(),
            ]);
        }

        $payment->refresh();

        // Gateway verification succeeded.
        if (
            $payment->status === 'success'
            && $payment->verified_at !== null
        ) {
            if (
                in_array(
                    $payment->payment_for,
                    ['application_fee', 'security_deposit'],
                    true
                )
            ) {
                if (! $payment->application_id) {
                    Log::error('Application payment has no application reference.', [
                        'payment_id' => $payment->id,
                    ]);

                    return view('frontend.pages.payment.failure', [
                        'message' => 'পেমেন্ট সফল হলেও আবেদনের তথ্য পাওয়া যায়নি। অনুগ্রহ করে কর্তৃপক্ষের সঙ্গে যোগাযোগ করুন।',
                    ]);
                }

                $application = PlotApplication::query()
                    ->with(['applicant', 'plot', 'allocation'])
                    ->findOrFail($payment->application_id);

                return view(
                    'frontend.pages.payment.success',
                    compact('payment', 'application')
                );
            }

            // Other payment types will be handled by their respective success views.
            Log::warning('No success view configured for this payment type.', [
                'payment_id' => $payment->id,
                'payment_for' => $payment->payment_for,
            ]);
        }

        // Payment was cancelled.
        if ($outcome === 'cancel') {
            return view('frontend.pages.payment.cancel');
        }

        return view('frontend.pages.payment.failure', [
            'message' => 'দুঃখিত, আপনার পেমেন্ট সফল হয়নি। অনুগ্রহ করে পেমেন্টের অবস্থা যাচাই করে পুনরায় চেষ্টা করুন।',
        ]);
    }


        
    /**
     * Verify payment with JanataPay and save the result.
     */
    private function verifyAndSave(
        Payment $payment,
        JanataPayClient $gateway
    ): void {
        if (
            $payment->status === 'success'
            && $payment->verified_at !== null
        ) {
            return;
        }

        if (blank($payment->transaction_token)) {
            throw new GatewayException(
                'Payment transaction token is missing.'
            );
        }

        // Verify payment with JanataPay.
        $response = $gateway->verify(
            $payment->merchant_invoice_id,
            $payment->transaction_token
        );

        $data = $response['data'] ?? [];

        Log::info('JanataPay Verification Response', [
            'payment_id' => $payment->id,
            'merchant_invoice_id' => $payment->merchant_invoice_id,
            'response' => $response,
        ]);

        if (! is_array($data) || empty($data)) {
            throw new GatewayException(
                'JanataPay verification response data is missing.'
            );
        }

        $statusCode = (string) (
            $data['transactionStatusCode'] ?? ''
        );

        $gatewayStatus = strtolower(trim((string) (
            $data['transactionStatus'] ?? 'unknown'
        )));

        $ftNumber = trim(
            (string) ($data['ftNumber'] ?? '')
        );

        try {
            $paidCents = Money::cents(
                $data['amount'] ?? null
            );
        } catch (InvalidArgumentException $exception) {
            $paidCents = 0;
        }

        $expectedCents = Money::cents($payment->amount);

        // Confirm successful transaction with exact amount.
        $confirmed = (
            $statusCode === '1003'
            && $gatewayStatus === 'success'
            && $paidCents === $expectedCents
        );

        // Map gateway status to our database ENUM values.
        $databaseStatus = match ($gatewayStatus) {
            'failed' => 'failed',
            'cancelled', 'canceled' => 'cancelled',
            'refunded' => 'refunded',
            default => 'pending',
        };

        DB::transaction(function () use (
            $payment,
            $response,
            $statusCode,
            $gatewayStatus,
            $ftNumber,
            $confirmed,
            $databaseStatus
        ) {
            /*
            * Lock payment record.
            */
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Prevent duplicate callback processing.
            if (
                $lockedPayment->status === 'success'
                && $lockedPayment->verified_at !== null
            ) {
                return;
            }

            /*
            * Check duplicate gateway transaction ID.
            */
            if ($confirmed && $ftNumber !== '') {
                $duplicate = Payment::query()
                    ->where('gateway_transaction_id', $ftNumber)
                    ->where('id', '!=', $lockedPayment->id)
                    ->exists();

                if ($duplicate) {
                    throw new GatewayException(
                        'This JanataPay transaction ID is already used.'
                    );
                }
            }

            /*
            * Prepare payment verification data.
            */
            $updateData = [
                'gateway_response' => $response,
                'verified_at' => now('Asia/Dhaka'),
                'status' => $databaseStatus,
                'remarks' => 'Payment verification completed. Gateway status: '
                    . $gatewayStatus
                    . ' (Code: '
                    . $statusCode
                    . ')',
            ];

            if (! $confirmed) {
                $lockedPayment->update($updateData);

                return;
            }

            $updateData['status'] = 'success';
            $updateData['paid_at'] = now('Asia/Dhaka');
            $updateData['remarks'] = 'JanataPay payment successfully verified.';

            if ($ftNumber !== '') {
                $updateData['gateway_transaction_id'] = $ftNumber;
            }

            $lockedPayment->update($updateData);

            /*
            * Application Fee:
            * Move application to submitted status.
            */
            if (
                $lockedPayment->payment_for === 'application_fee'
                && $lockedPayment->application_id !== null
            ) {
                PlotApplication::query()
                    ->whereKey($lockedPayment->application_id)
                    ->where('status', 'pending_payment')
                    ->update([
                        'status' => 'submitted',
                    ]);

                return;
            }

            /*
            * Security Deposit:
            * Automatically create Plot Allocation.
            */
            if (
                $lockedPayment->payment_for === 'security_deposit'
                && $lockedPayment->application_id !== null
            ) {
                // Lock application.
                $application = PlotApplication::query()
                    ->whereKey($lockedPayment->application_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! in_array($application->status, [
                    'approved',
                    'allocated',
                ], true)) {
                    throw new GatewayException(
                        'Application is not approved for plot allocation.'
                    );
                }

                /*
                * Check existing active allocation.
                * This also makes repeated processing safe.
                */
                $existingAllocation = PlotAllocation::query()
                    ->where('application_id', $application->id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first();

                if ($existingAllocation) {
                    if (
                        $existingAllocation->applicant_id !== $application->applicant_id
                        || $existingAllocation->plot_id !== $application->plot_id
                    ) {
                        throw new GatewayException(
                            'An active allocation already exists with different details.'
                        );
                    }

                    // Keep application and plot status consistent.
                    Plot::query()
                        ->whereKey($existingAllocation->plot_id)
                        ->lockForUpdate()
                        ->firstOrFail()
                        ->update([
                            'status' => 'allocated',
                        ]);

                    $application->update([
                        'status' => 'allocated',
                    ]);

                    return;
                }

                // Lock the final plot selected in the application.
                $plot = Plot::query()
                    ->whereKey($application->plot_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($plot->status !== 'available') {
                    throw new GatewayException(
                        'The selected plot is no longer available. Please contact the office.'
                    );
                }

                /*
                * Create allocation.
                */
                PlotAllocation::create([
                    'application_id' => $application->id,
                    'applicant_id' => $application->applicant_id,
                    'plot_id' => $plot->id,
                    'active_applicant_id' => $application->applicant_id,
                    'active_plot_id' => $plot->id,
                    'allocated_by' => null,
                    'allocated_at' => now('Asia/Dhaka')->toDateString(),
                    'start_date' => now('Asia/Dhaka')->toDateString(),
                    'end_date' => null,
                    'status' => 'active',
                    'remarks' => 'Automatically allocated after successful security deposit payment.',
                ]);

                /*
                * Update plot and application status.
                */
                $plot->update([
                    'status' => 'allocated',
                ]);

                $application->update([
                    'status' => 'allocated',
                ]);
            }
        }, 5);
    }

}
