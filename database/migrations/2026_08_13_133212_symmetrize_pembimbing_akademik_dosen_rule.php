<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION 5 — pembimbing_akademik.dosen_id sebelumnya CASCADE, padahal
 * pembimbing_akademik.mahasiswa_id sudah RESTRICT di tabel yang sama.
 * Menyamakan aturan agar dosen tidak bisa dihapus selama masih tercatat
 * sebagai pembimbing akademik (baik histori maupun aktif).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembimbing_akademik', function (Blueprint $table) {
            $table->dropForeign(['dosen_id']);
            $table->foreign('dosen_id')->references('id')->on('trx_dosen')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('pembimbing_akademik', function (Blueprint $table) {
            $table->dropForeign(['dosen_id']);
            $table->foreign('dosen_id')->references('id')->on('trx_dosen')->onDelete('cascade');
        });
    }
};
