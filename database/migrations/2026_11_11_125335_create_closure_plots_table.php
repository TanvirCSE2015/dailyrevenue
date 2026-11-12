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
        Schema::create('closure_plots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('office_closure_id')
                ->constrained('office_closures')
                ->cascadeOnDelete();

            $table->foreignId('plot_id')
                ->constrained('plots')
                ->restrictOnDelete();

            $table->timestamps();

            $table->unique(
                ['office_closure_id', 'plot_id'],
                'closure_plot_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closure_plots');
    }
};
