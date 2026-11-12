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
        Schema::create('absence_notices', function (Blueprint $table) {
            $table->id();

            $table->string('notice_no')->unique();

            $table->foreignId('allocation_id')
                ->constrained('plot_allocations')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('consecutive_absent_days')
                ->default(7);

            $table->date('absence_start_date');

            $table->date('notice_date');

            $table->date('response_deadline')->nullable();

            $table->enum('status', [
                'draft',
                'issued',
                'acknowledged',
                'responded',
                'closed',
                'cancelled',
            ])->default('draft');

            $table->text('notice_content');

            $table->text('applicant_response')->nullable();

            $table->timestamp('responded_at')->nullable();

            $table->foreignId('issued_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index(['allocation_id', 'status']);
            $table->index('notice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absence_notices');
    }
};
