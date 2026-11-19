<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PlotApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlotApplicationReviewService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function markUnderReview(int $applicationId): PlotApplication
    {
        return DB::transaction(function () use ($applicationId) {

            $application = PlotApplication::query()
                ->lockForUpdate()
                ->findOrFail($applicationId);

            if (! in_array($application->status, [
                'submitted',
                'returned',
            ], true)) {
                throw ValidationException::withMessages([
                    'application' => 'এই আবেদনের বর্তমান অবস্থায় যাচাই শুরু করা যাবে না।',
                ]);
            }

            $application->update([
                'status' => 'under_review',
            ]);

            return $application->refresh();

        }, 5);
    }

     /**
     * Approve an application.
     */
    public function approve(
        int $applicationId,
        ?string $remarks = null
    ): PlotApplication {
        return DB::transaction(function () use ($applicationId, $remarks) {

            $application = PlotApplication::query()
                ->lockForUpdate()
                ->findOrFail($applicationId);

            $this->ensureReviewable($application);

            // Verify successful application fee payment.
            $hasSuccessfulFeePayment = Payment::query()
                ->where('application_id', $application->id)
                ->where('applicant_id', $application->applicant_id)
                ->where('payment_for', 'application_fee')
                ->where('status', 'success')
                ->where('amount', '>=', $application->application_fee_amount)
                ->exists();

            if (! $hasSuccessfulFeePayment) {
                throw ValidationException::withMessages([
                    'application' => 'আবেদন ফি সফলভাবে পরিশোধিত হয়নি। আবেদন অনুমোদন করা যাবে না।',
                ]);
            }

            // Save review history.
            $application->reviews()->create([
                'reviewer_id' => Auth::id(),
                'decision' => 'approved',
                'remarks' => $remarks,
                'reviewed_at' => now(),
            ]);

            // Update application status.
            $application->update([
                'status' => 'approved',
                'admin_note' => $remarks,
            ]);

            $application->applicant()->update([
                'is_active' => true,
            ]);

            return $application->refresh();
        }, 5);
    }

    /**
     * Reject an application.
     */
    public function reject(
        int $applicationId,
        string $remarks
    ): PlotApplication {
        return DB::transaction(function () use ($applicationId, $remarks) {

            $application = PlotApplication::query()
                ->lockForUpdate()
                ->findOrFail($applicationId);

            $this->ensureReviewable($application);

            if (blank($remarks)) {
                throw ValidationException::withMessages([
                    'remarks' => 'আবেদন বাতিলের কারণ লিখতে হবে।',
                ]);
            }

            $application->reviews()->create([
                'reviewer_id' => Auth::id(),
                'decision' => 'rejected',
                'remarks' => $remarks,
                'reviewed_at' => now(),
            ]);

            $application->update([
                'status' => 'rejected',
                'admin_note' => $remarks,
            ]);

            return $application->refresh();
        }, 5);
    }

    /**
     * Return an application for correction.
     */
    public function returnForCorrection(
        int $applicationId,
        string $remarks
    ): PlotApplication {
        return DB::transaction(function () use ($applicationId, $remarks) {

            $application = PlotApplication::query()
                ->lockForUpdate()
                ->findOrFail($applicationId);

            $this->ensureReviewable($application);

            if (blank($remarks)) {
                throw ValidationException::withMessages([
                    'remarks' => 'সংশোধনের নির্দেশনা লিখতে হবে।',
                ]);
            }

            $application->reviews()->create([
                'reviewer_id' => Auth::id(),
                'decision' => 'returned',
                'remarks' => $remarks,
                'reviewed_at' => now(),
            ]);

            $application->update([
                'status' => 'returned',
                'admin_note' => $remarks,
            ]);

            return $application->refresh();
        }, 5);
    }

    /**
     * Ensure application can be reviewed.
     */
    protected function ensureReviewable(
        PlotApplication $application
    ): void {
        if (! in_array($application->status, [
            'submitted',
            'under_review',
            'returned',
        ], true)) {
            throw ValidationException::withMessages([
                'application' => 'এই আবেদনের বর্তমান অবস্থায় পর্যালোচনা করা যাবে না।',
            ]);
        }
    }
}
