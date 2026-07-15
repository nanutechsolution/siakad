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
        Schema::table('keuangan_saldo_transactions', function (Blueprint $table) {
            $table->foreign(['saldo_id'])->references(['id'])->on('keuangan_saldos')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan_saldo_transactions', function (Blueprint $table) {
            $table->dropForeign('keuangan_saldo_transactions_saldo_id_foreign');
        });
    }
};
