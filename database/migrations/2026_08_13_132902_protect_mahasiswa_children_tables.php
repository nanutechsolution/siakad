<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION 2 — Lindungi seluruh turunan langsung `mahasiswas` yang berisi
 * data akademik/keuangan permanen dari ON DELETE CASCADE.
 *
 * Tabel yang diubah dari CASCADE -> RESTRICT:
 *  - akademik_transkrip.mahasiswa_id      (transkrip resmi)
 *  - riwayat_status_mahasiswas.mahasiswa_id (sumber IPS/IPK)
 *  - riwayat_prodi_mahasiswas.mahasiswa_id
 *  - mahasiswa_kelas.mahasiswa_id + kelas_id (histori penempatan kelas, append-only)
 *  - academic_history_logs.mahasiswa_id + tahun_akademik_id (log audit)
 *  - keuangan_saldos.mahasiswa_id
 *  - keuangan_mahasiswa_beasiswas.mahasiswa_id
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('akademik_transkrip', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('restrict');
        });

        Schema::table('riwayat_status_mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('restrict');
        });

        Schema::table('riwayat_prodi_mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('restrict');
        });

        Schema::table('mahasiswa_kelas', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->dropForeign(['kelas_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('restrict');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('restrict');
        });

        Schema::table('academic_history_logs', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->dropForeign(['tahun_akademik_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('restrict');
            $table->foreign('tahun_akademik_id')->references('id')->on('ref_tahun_akademik')->onDelete('restrict');
        });

        Schema::table('keuangan_saldos', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('restrict');
        });

        Schema::table('keuangan_mahasiswa_beasiswas', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('akademik_transkrip', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('cascade');
        });

        Schema::table('riwayat_status_mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('cascade');
        });

        Schema::table('riwayat_prodi_mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('cascade');
        });

        Schema::table('mahasiswa_kelas', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->dropForeign(['kelas_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
        });

        Schema::table('academic_history_logs', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->dropForeign(['tahun_akademik_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('cascade');
            $table->foreign('tahun_akademik_id')->references('id')->on('ref_tahun_akademik')->onDelete('cascade');
        });

        Schema::table('keuangan_saldos', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('cascade');
        });

        Schema::table('keuangan_mahasiswa_beasiswas', function (Blueprint $table) {
            $table->dropForeign(['mahasiswa_id']);
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->onDelete('cascade');
        });
    }
};
