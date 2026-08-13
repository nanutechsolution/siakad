<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION 9 — Pembersihan akhir menuju production-ready.
 *
 * Menutup seluruh sisa relasi berstatus 🟡 PERLU PERHATIAN di laporan audit
 * yang belum diubah oleh Migration 1-8:
 *
 *  1. jadwal_kuliah_dosen.jadwal_kuliah_id : CASCADE -> RESTRICT
 *     (riwayat "siapa mengajar" tidak boleh ikut hilang saat jadwal dihapus)
 *  2. jadwal_ujian_pesertas.krs_detail_id : CASCADE -> RESTRICT
 *     (data peserta ujian adalah bukti keikutsertaan resmi)
 *  3. mahasiswas -> generator_logs.mahasiswa_id : CASCADE -> RESTRICT
 *  4. mahasiswas -> sinkronisasi_logs.mahasiswa_id : CASCADE -> RESTRICT
 *  5. mahasiswas -> sinkronisasi_review_items.mahasiswa_id : CASCADE -> RESTRICT
 *     (ketiganya adalah audit trail keuangan/akademik — setelah migration
 *     ini, TIDAK ADA LAGI tabel log yang bisa hilang otomatis karena
 *     mahasiswa dihapus)
 *
 * Setelah migration ini, seluruh child langsung dari `mahasiswas` yang
 * masih ON DELETE CASCADE hanyalah tabel yang MEMANG secara desain tidak
 * bermakna tanpa parent-nya (mahasiswa_biodata, profile_change_requests,
 * lpm_survey_jawaban, lpm_edom_progress) — ini SENGAJA dibiarkan CASCADE.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_kuliah_dosen', function (Blueprint $table) {
            $table->dropForeign(['jadwal_kuliah_id']);
            $table->foreign('jadwal_kuliah_id')->references('id')->on('jadwal_kuliah')->onDelete('restrict');
        });

        Schema::table('jadwal_ujian_pesertas', function (Blueprint $table) {
            $table->dropForeign(['krs_detail_id']);
            $table->foreign('krs_detail_id')->references('id')->on('krs_detail')->onDelete('restrict');
        });

        Schema::table('generator_logs', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('restrict');
        });

        Schema::table('sinkronisasi_logs', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('restrict');
        });

        Schema::table('sinkronisasi_review_items', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('sinkronisasi_review_items', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('cascade');
        });

        Schema::table('sinkronisasi_logs', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('cascade');
        });

        Schema::table('generator_logs', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('cascade');
        });

        Schema::table('jadwal_ujian_pesertas', function (Blueprint $table) {
            $table->dropForeign(['krs_detail_id']);
            $table->foreign('krs_detail_id')->references('id')->on('krs_detail')->onDelete('cascade');
        });

        Schema::table('jadwal_kuliah_dosen', function (Blueprint $table) {
            $table->dropForeign(['jadwal_kuliah_id']);
            $table->foreign('jadwal_kuliah_id')->references('id')->on('jadwal_kuliah')->onDelete('cascade');
        });
    }
};
