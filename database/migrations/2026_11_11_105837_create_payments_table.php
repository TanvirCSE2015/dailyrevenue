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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('payment_no')->unique();

            $table->foreignId('applicant_id')
                ->constrained('applicants')
                ->restrictOnDelete();

            $table->foreignId('application_id')
                ->nullable()
                ->constrained('plot_applications')
                ->nullOnDelete();

            $table->foreignId('allocation_id')
                ->nullable()
                ->constrained('plot_allocations')
                ->nullOnDelete();

            $table->enum('payment_for', [
                'application_fee',
                'security_deposit',
                'daily_rent',
                'security_deposit_refund',
                'other',
            ]);

            $table->enum('gateway', [
                'bkash',
                'nagad',
                'bangla_qr',
                'manual',
            ]);

            $table->string('merchant_invoice_id')->unique();

            $table->string('gateway_transaction_id')
                ->nullable()
                ->unique();

            $table->decimal('amount', 12, 2);

            $table->string('currency', 3)->default('BDT');

            $table->enum('status', [
                'initiated',
                'pending',
                'success',
                'failed',
                'cancelled',
                'refunded',
            ])->default('initiated');

            $table->timestamp('paid_at')->nullable();

            $table->timestamp('verified_at')->nullable();

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('gateway_response')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['applicant_id', 'payment_for']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
