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
        Schema::create('application_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')
                ->constrained('plot_applications')
                ->cascadeOnDelete();

            $table->foreignId('reviewer_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('decision', [
                'approved',
                'rejected',
                'returned',
            ]);

            $table->text('remarks')->nullable();

            $table->timestamp('reviewed_at');

            $table->timestamps();

            $table->index(['application_id', 'reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_reviews');
    }
};
