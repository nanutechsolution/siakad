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
        Schema::create('pembayaran_mahasiswas', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->char('tagihan_id', 36)->index('pembayaran_mahasiswas_tagihan_id_foreign');
            $table->decimal('nominal_bayar', 19);
            $table->dateTime('tanggal_bayar');
            $table->string('metode_pembayaran', 20)->default('MANUAL');
            $table->string('bukti_bayar_path')->nullable();
            $table->string('keterangan_pengirim')->nullable();
            $table->unsignedTinyInteger('status_verifikasi_id')->default(1)->index();
            $table->char('verified_by', 36)->nullable()->index('pembayaran_mahasiswas_verified_by_foreign');
            $table->dateTime('verified_at')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_mahasiswas');
    }
};
