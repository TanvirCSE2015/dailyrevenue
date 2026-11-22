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
        Schema::create('plot_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')
                ->constrained('plot_applications')
                ->restrictOnDelete();

            $table->foreignId('applicant_id')
                ->constrained('applicants')
                ->restrictOnDelete();

            $table->foreignId('plot_id')
                ->constrained('plots')
                ->restrictOnDelete();

            // Active allocation uniqueness
            $table->unsignedBigInteger('active_applicant_id')
                ->nullable()
                ->unique();

            $table->unsignedBigInteger('active_plot_id')
                ->nullable()
                ->unique();

            $table->foreign('active_applicant_id')
                ->references('id')
                ->on('applicants')
                ->nullOnDelete();

            $table->foreign('active_plot_id')
                ->references('id')
                ->on('plots')
                ->nullOnDelete();

            $table->foreignId('allocated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('allocated_at');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('last_synced_date')->nullable();

            $table->enum('status', [
                'active',
                'surrendered',
                'cancelled',
                'expired',
            ])->default('active');

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['applicant_id', 'status']);
            $table->index(['plot_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_allocations');
    }
};
