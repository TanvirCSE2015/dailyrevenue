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
        Schema::create('office_closures', function (Blueprint $table) {
            $table->id();

            $table->string('closure_no')->unique();

            $table->string('title');

            $table->enum('closure_type', [
                'official_holiday',
                'emergency',
                'weather',
                'other',
            ])->default('official_holiday');

            $table->date('start_date');

            $table->date('end_date');

            $table->boolean('is_global')->default(true);

            $table->text('reason')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_closures');
    }
};
