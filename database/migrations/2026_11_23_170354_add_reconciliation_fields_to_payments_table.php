<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('reconciliation_status', [
                'not_started',
                'pending',
                'completed',
                'failed',
            ])->default('not_started')->after('status');

            $table->unsignedSmallInteger('reconciliation_attempts')
                ->default(0)
                ->after('reconciliation_status');

            $table->timestamp('reconciled_at')
                ->nullable()
                ->after('reconciliation_attempts');

            $table->text('reconciliation_error')
                ->nullable()
                ->after('reconciled_at');

            $table->index(
                ['reconciliation_status', 'payment_for'],
                'payments_reconciliation_status_for_index'
            );
        });

        // Existing successful payments need reconciliation review.
        DB::table('payments')
            ->where('status', 'success')
            ->whereNotNull('verified_at')
            ->update([
                'reconciliation_status' => 'pending',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
             $table->dropIndex(
                'payments_reconciliation_status_for_index'
            );

            $table->dropColumn([
                'reconciliation_status',
                'reconciliation_attempts',
                'reconciled_at',
                'reconciliation_error',
            ]);
        });
    }
};
