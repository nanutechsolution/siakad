<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION 4 — `kelas` (rombel permanen) belum punya deleted_at, dan
 * jadwal_kuliah/perkuliahan_sesi/jadwal_ujians masih CASCADE dari parent-nya.
 * Migration ini:
 *   1. Menambahkan deleted_at pada kelas (sesuai keputusan arsitektur:
 *      kelas = rombel permanen per angkatan+prodi+program, tidak boleh hard-delete).
 *   2. Mengubah jadwal_kuliah.kelas_id, perkuliahan_sesi.jadwal_kuliah_id,
 *      jadwal_ujians.jadwal_kuliah_id dari CASCADE -> RESTRICT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('jadwal_kuliah', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('restrict');
        });

        Schema::table('perkuliahan_sesi', function (Blueprint $table) {
            $table->dropForeign(['jadwal_kuliah_id']);
            $table->foreign('jadwal_kuliah_id')->references('id')->on('jadwal_kuliah')->onDelete('restrict');
        });

        Schema::table('jadwal_ujians', function (Blueprint $table) {
            $table->dropForeign(['jadwal_kuliah_id']);
            $table->foreign('jadwal_kuliah_id')->references('id')->on('jadwal_kuliah')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_ujians', function (Blueprint $table) {
            $table->dropForeign(['jadwal_kuliah_id']);
            $table->foreign('jadwal_kuliah_id')->references('id')->on('jadwal_kuliah')->onDelete('cascade');
        });

        Schema::table('perkuliahan_sesi', function (Blueprint $table) {
            $table->dropForeign(['jadwal_kuliah_id']);
            $table->foreign('jadwal_kuliah_id')->references('id')->on('jadwal_kuliah')->onDelete('cascade');
        });

        Schema::table('jadwal_kuliah', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
        });

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
