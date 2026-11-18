<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Payment;
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
        Log::info('JanataPay Verification Response', [
            'payment_id' =>$gateway,
        ]);
        $payment = Payment::query()
            ->where('merchant_invoice_id', $referenceId)
            ->first();

        try {
            $this->verifyAndSave($payment, $gateway);
        } catch (\Throwable $exception) {
            Log::error('JanataPay payment verification error.', [
                'payment_id' => $payment->id,
                'merchant_invoice_id' => $payment->merchant_invoice_id,
                'message' => $exception->getMessage(),
            ]);
        }

        $payment->refresh();

        // Gateway verification succeeded.
        if (
            $payment->status === 'success'
            && $payment->verified_at !== null
        ) {
            $application = PlotApplication::query()
                ->with(['applicant', 'plot'])
                ->findOrFail($payment->application_id);

            return view(
                'frontend.pages.payment.success',
                compact('payment', 'application')
            );
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

        // Actual transaction information is inside response.data.
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

        $gatewayStatus = (string) (
            $data['transactionStatus'] ?? 'Unknown'
        );

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

        // Confirm only successful transactions with exact amount.
        $confirmed = (
            $statusCode === '1003'
            && strtolower($gatewayStatus) === 'success'
            && $paidCents === $expectedCents
        );

        DB::transaction(function () use (
            $payment,
            $response,
            $data,
            $statusCode,
            $gatewayStatus,
            $ftNumber,
            $confirmed
        ) {
            $lockedPayment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Avoid duplicate processing.
            if (
                $lockedPayment->status === 'success'
                && $lockedPayment->verified_at !== null
            ) {
                return;
            }

            // Check duplicate gateway transaction ID.
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

            $updateData = [
                'gateway_response' => $response,
                'verified_at' => now('Asia/Dhaka'),
                'status' => $gatewayStatus,
                'remarks' => $confirmed
                    ? 'JanataPay payment successfully verified.'
                    : 'Payment not confirmed. Gateway status: '
                        . $gatewayStatus
                        . ' (Code: '
                        . $statusCode
                        . ')',
            ];

            if ($confirmed) {
                $updateData['status'] = 'success';
                $updateData['paid_at'] = now('Asia/Dhaka');

                if ($ftNumber !== '') {
                    $updateData['gateway_transaction_id'] = $ftNumber;
                }
            }

            $lockedPayment->update($updateData);

            // Update application after successful application fee payment.
            if (
                $confirmed
                && $lockedPayment->payment_for === 'application_fee'
                && $lockedPayment->application_id !== null
            ) {
                PlotApplication::query()
                    ->whereKey($lockedPayment->application_id)
                    ->where('status', 'pending_payment')
                    ->update([
                        'status' => 'submitted',
                    ]);
            }
        });
    }
}
