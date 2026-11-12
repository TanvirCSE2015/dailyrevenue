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
        Schema::create('daily_rent_ledgers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('allocation_id')
                ->constrained('plot_allocations')
                ->restrictOnDelete();

            $table->foreignId('attendance_id')
                ->nullable()
                ->constrained('daily_attendances')
                ->nullOnDelete();

            $table->foreignId('rate_history_id')
                ->nullable()
                ->constrained('rate_histories')
                ->nullOnDelete();

            $table->date('rent_date');

            $table->decimal('base_amount', 12, 2);

            $table->decimal('adjustment_amount', 12, 2)
                ->default(0);

            $table->decimal('net_amount', 12, 2);

            $table->enum('status', [
                'pending',
                'partial',
                'paid',
                'waived',
            ])->default('pending');

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(
                ['allocation_id', 'rent_date'],
                'daily_rent_allocation_date_unique'
            );

            $table->index(['rent_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_rent_ledgers');
    }
};
