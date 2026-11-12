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
        Schema::create('daily_attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('allocation_id')
                ->constrained('plot_allocations')
                ->restrictOnDelete();

            $table->date('attendance_date');

            $table->enum('attendance_status', [
                'present',
                'absent',
                'excused',
            ])->default('present');

            $table->foreignId('marked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('marked_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(
                ['allocation_id', 'attendance_date'],
                'daily_attendance_allocation_date_unique'
            );

            $table->index([
                'attendance_date',
                'attendance_status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_attendances');
    }
};
