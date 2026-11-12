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
        Schema::create('rent_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('daily_rent_ledger_id')
                ->constrained('daily_rent_ledgers')
                ->restrictOnDelete();

            $table->enum('adjustment_type', [
                'illness',
                'absence',
                'rain',
                'other',
            ]);

            $table->enum('adjustment_mode', [
                'full',
                'partial',
            ]);

            $table->decimal('requested_amount', 12, 2)
                ->nullable();

            $table->decimal('approved_amount', 12, 2)
                ->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            $table->text('reason');

            $table->string('attachment_path')->nullable();

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

            $table->timestamps();

            $table->index([
                'daily_rent_ledger_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_adjustments');
    }
};
