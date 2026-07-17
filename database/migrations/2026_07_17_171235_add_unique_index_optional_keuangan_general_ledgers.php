<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OPSIONAL. LedgerService sudah aman tanpa ini (idempotency dijamin
 * oleh Cache::lock per mahasiswa di level aplikasi). Index ini murni
 * untuk mempercepat query cariEntriIdempotent() dan sebagai jaring
 * pengaman kedua di level DB kalau suatu saat ada proses yang menulis
 * ke keuangan_general_ledgers TANPA lewat LedgerService (usahakan
 * jangan pernah terjadi, tapi kalau terjadi, unique constraint ini
 * akan gagal dengan jelas alih-alih diam-diam mendobel data).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keuangan_general_ledgers', function (Blueprint $table) {
            $table->unique(['referensi_dokumen', 'tipe_transaksi'], 'uniq_ledger_referensi_tipe');
        });
    }

    public function down(): void
    {
        Schema::table('keuangan_general_ledgers', function (Blueprint $table) {
            $table->dropUnique('uniq_ledger_referensi_tipe');
        });
    }
};