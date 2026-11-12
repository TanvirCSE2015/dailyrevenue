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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            $table->string('refund_no')->unique();

            $table->foreignId('allocation_id')
                ->nullable()
                ->constrained('plot_allocations')
                ->nullOnDelete();

            $table->foreignId('cancellation_id')
                ->nullable()
                ->constrained('allocation_cancellations')
                ->nullOnDelete();

            $table->foreignId('payment_id')
                ->nullable()
                ->constrained('payments')
                ->nullOnDelete();

            $table->enum('refund_type', [
                'security_deposit',
                'payment_refund',
                'other',
            ]);

            $table->enum('refund_method', [
                'bkash',
                'nagad',
                'bank_transfer',
                'cash',
                'other',
            ])->default('bkash');

            $table->decimal('amount', 12, 2);

            $table->string('transaction_reference')->nullable();

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'cancelled',
            ])->default('pending');

            $table->date('refund_date')->nullable();

            $table->foreignId('requested_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->foreignId('processed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('processed_at')->nullable();

            $table->text('reason')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['allocation_id', 'status']);
            $table->index(['refund_type', 'refund_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
