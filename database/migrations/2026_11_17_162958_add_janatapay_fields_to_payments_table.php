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
        DB::statement("
            ALTER TABLE payments
            MODIFY gateway ENUM(
                'bkash',
                'nagad',
                'bangla_qr',
                'manual',
                'janata_pay'
            ) NOT NULL
        ");

        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_token')
                ->nullable()
                ->unique()
                ->after('merchant_invoice_id');

            $table->text('gateway_url')
                ->nullable()
                ->after('transaction_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payments')
            ->where('gateway', 'janata_pay')
            ->update(['gateway' => 'manual']);

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['transaction_token']);
            $table->dropColumn([
                'transaction_token',
                'gateway_url',
            ]);
        });

        DB::statement("
            ALTER TABLE payments
            MODIFY gateway ENUM(
                'bkash',
                'nagad',
                'bangla_qr',
                'manual'
            ) NOT NULL
        ");
    }
};
