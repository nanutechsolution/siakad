<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dibuat SEKALI saat mahasiswa klik "Bayar via Midtrans" (initiate),
 * dipakai webhook untuk resolve order_id -> tagihan mana, dan untuk
 * cross-check nominal (pertahanan terhadap notifikasi yang di-tampering).
 *
 * TIDAK diupdate setelah dibuat — status akhir pembayaran hidup di
 * pembayaran_mahasiswas (lewat idempotency_key = order_id ini), bukan
 * di tabel ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('midtrans_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_id', 100)->unique();
            $table->char('tagihan_id', 36);
            $table->string('tagihan_type', 100);
            $table->char('mahasiswa_id', 36);
            $table->decimal('nominal', 19, 2);
            $table->text('snap_token')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tagihan_type', 'tagihan_id']);
            $table->index('mahasiswa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('midtrans_transactions');
    }
};
