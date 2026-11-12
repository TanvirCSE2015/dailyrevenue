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
        Schema::create('payment_gateway_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_id')
                ->constrained('payments')
                ->cascadeOnDelete();

            $table->string('gateway_event_id')
                ->nullable()
                ->unique();

            $table->string('event_type');

            $table->json('payload')->nullable();

            $table->boolean('signature_valid')->default(false);

            $table->boolean('is_processed')->default(false);

            $table->timestamp('processed_at')->nullable();

            $table->text('processing_error')->nullable();

            $table->timestamps();

            $table->index(['payment_id', 'is_processed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_events');
    }
};
