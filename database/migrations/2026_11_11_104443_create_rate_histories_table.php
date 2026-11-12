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
        Schema::create('rate_histories', function (Blueprint $table) {
            $table->id();

            $table->enum('rate_type', [
                'application_fee',
                'security_deposit',
                'daily_rent',
            ]);

            $table->decimal('amount', 12, 2);

            $table->date('effective_from');
            $table->date('effective_until')->nullable();

            $table->text('description')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['rate_type', 'effective_from'],
                'rate_type_effective_from_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_histories');
    }
};
