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
        Schema::create('keuangan_general_ledgers', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('mahasiswa_id', 36);
            $table->string('referensi_dokumen', 100)->index();
            $table->enum('tipe_transaksi', ['TAGIHAN', 'PEMBAYARAN', 'ADJUSTMENT', 'REFUND']);
            $table->decimal('debit', 19)->default(0);
            $table->decimal('kredit', 19)->default(0);
            $table->decimal('saldo_berjalan', 19);
            $table->text('keterangan');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['mahasiswa_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_general_ledgers');
    }
};
