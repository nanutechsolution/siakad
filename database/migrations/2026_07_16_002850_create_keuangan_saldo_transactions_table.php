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
        Schema::create('keuangan_saldo_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('saldo_id', 36)->index('keuangan_saldo_transactions_saldo_id_foreign');
            $table->enum('tipe', ['IN', 'OUT']);
            $table->decimal('nominal', 15);
            $table->string('referensi_id')->nullable();
            $table->string('keterangan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_saldo_transactions');
    }
};
