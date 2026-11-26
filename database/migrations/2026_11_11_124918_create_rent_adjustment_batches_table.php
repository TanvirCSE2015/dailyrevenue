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
        Schema::create('rent_adjustment_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applicant_id')
                ->constrained('applicants')
                ->restrictOnDelete();

            $table->enum('adjustment_type', [
                'illness',
                'absence',
                'rain',
                'other',
            ]);

            $table->enum('adjustment_basis', [
                'days',
                'dates',
                'amount',
            ]);

            $table->unsignedSmallInteger('adjustment_days')
                ->nullable();

            $table->decimal('requested_amount', 12, 2)
                ->nullable();

            $table->decimal('approved_amount', 12, 2)
                ->default(0);

            $table->text('reason');

            $table->string('attachment_path')->nullable();

            $table->foreignId('requested_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('requested_at')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            $table->text('approval_remarks')->nullable();

            $table->timestamps();

            $table->index([
                'applicant_id',
                'created_at',
            ]);

            $table->index([
                'adjustment_basis',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_adjustment_batches');
    }
};
