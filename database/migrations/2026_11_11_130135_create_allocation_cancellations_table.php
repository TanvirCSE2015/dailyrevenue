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
        Schema::create('allocation_cancellations', function (Blueprint $table) {
            $table->id();

            $table->string('cancellation_no')->unique();

            $table->foreignId('allocation_id')
                ->constrained('plot_allocations')
                ->restrictOnDelete();

            $table->foreignId('absence_notice_id')
                ->nullable()
                ->constrained('absence_notices')
                ->nullOnDelete();

            $table->enum('cancellation_type', [
                'voluntary',
                'absence',
                'authority_decision',
                'other',
            ]);

            $table->date('requested_date');

            $table->date('effective_date')->nullable();

            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'completed',
            ])->default('pending');

            $table->text('reason');

            $table->text('applicant_statement')->nullable();

            $table->foreignId('requested_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->text('approval_remarks')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->foreignId('completed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('completion_remarks')->nullable();

            $table->timestamps();

            $table->index(['allocation_id', 'status']);
            $table->index('requested_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocation_cancellations');
    }
};
