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
        Schema::create('plots', function (Blueprint $table) {
            $table->id();

            $table->string('plot_number')->unique();
            $table->string('name')->nullable();

            $table->string('location')->nullable();

            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();

            $table->enum('status', [
                'available',
                'reserved',
                'allocated',
                'maintenance',
                'inactive',
            ])->default('available');

            $table->text('description')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plots');
    }
};
