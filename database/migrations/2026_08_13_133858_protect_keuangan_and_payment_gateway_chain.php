<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION 8 — Menutup celah modul keuangan yang belum dibahas di
 * Migration 1-7:
 *
 *  1. mahasiswas -> tagihan_non_regulers: CASCADE -> RESTRICT
 *  2. tagihan_mahasiswas -> tagihan_mahasiswas_details: CASCADE -> RESTRICT
 *  3. tagihan_mahasiswas -> keuangan_adjustments: CASCADE -> RESTRICT
 *  4. tagihan_non_regulers -> tagihan_non_reguler_details: CASCADE -> RESTRICT
 *  5. Tambah deleted_at pada generator_batches & sinkronisasi_batches
 *     (batch header ini adalah "akar" dari log audit, sebelumnya tidak
 *     punya soft delete sama sekali).
 *  6. Tambah deleted_at pada midtrans_transactions & midtrans_gateway_logs
 *     agar log gateway pembayaran tidak bisa hilang lewat hard delete
 *     tidak sengaja (tabel ini tidak punya FK constraint sama sekali,
 *     jadi satu-satunya jaring pengaman adalah soft delete + Policy).
 *
 * CATATAN: mahasiswas -> generator_logs dan mahasiswas -> sinkronisasi_logs /
 * sinkronisasi_review_items SENGAJA dibiarkan CASCADE, karena log ini murni
 * turunan proses batch (bukan data transaksi keuangan primer) dan sudah
 * dilindungi secara tidak langsung lewat RESTRICT di mahasiswas itu sendiri
 * (Migration 1). Jika Anda ingin log ini juga immutable, ubah ke RESTRICT
 * dengan pola yang sama seperti di bawah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihan_non_regulers', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('restrict');
        });

        Schema::table('tagihan_mahasiswas_details', function (Blueprint $table) {
            $table->dropForeign(['tagihan_id']);
            $table->foreign('tagihan_id')->references('id')->on('tagihan_mahasiswas')->onDelete('restrict');
        });

        Schema::table('keuangan_adjustments', function (Blueprint $table) {
            $table->dropForeign(['tagihan_id']);
            $table->foreign('tagihan_id')->references('id')->on('tagihan_mahasiswas')->onDelete('restrict');
        });

        Schema::table('tagihan_non_reguler_details', function (Blueprint $table) {
            $table->dropForeign(['tagihan_id']);
            $table->foreign('tagihan_id')->references('id')->on('tagihan_non_regulers')->onDelete('restrict');
        });

        Schema::table('generator_batches', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('sinkronisasi_batches', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('midtrans_transactions', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('midtrans_gateway_logs', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('midtrans_gateway_logs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('midtrans_transactions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('sinkronisasi_batches', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('generator_batches', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('tagihan_non_reguler_details', function (Blueprint $table) {
            $table->dropForeign(['tagihan_id']);
            $table->foreign('tagihan_id')->references('id')->on('tagihan_non_regulers')->onDelete('cascade');
        });

        Schema::table('keuangan_adjustments', function (Blueprint $table) {
            $table->dropForeign(['tagihan_id']);
            $table->foreign('tagihan_id')->references('id')->on('tagihan_mahasiswas')->onDelete('cascade');
        });

        Schema::table('tagihan_mahasiswas_details', function (Blueprint $table) {
            $table->dropForeign(['tagihan_id']);
            $table->foreign('tagihan_id')->references('id')->on('tagihan_mahasiswas')->onDelete('cascade');
        });

        Schema::table('tagihan_non_regulers', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('cascade');
        });
    }
};
