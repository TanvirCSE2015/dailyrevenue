<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plot_applications', function (Blueprint $table) {
            $table->id();

            $table->string('application_no')->unique();

            $table->foreignId('applicant_id')
                ->constrained('applicants')
                ->restrictOnDelete();

            $table->foreignId('plot_id')
                ->constrained('plots')
                ->restrictOnDelete();

            $table->date('applied_at');
            $table->decimal('application_fee_amount', 10, 2)
                ->default(100);

            $table->enum('status', [
                'pending_payment',
                'payment_submitted',
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'cancelled',
                'allocated',
            ])->default('pending_payment');

            $table->text('applicant_note')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['applicant_id', 'status']);
            $table->index(['plot_id', 'status']);
            $table->index('applied_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_applications');
    }
};
