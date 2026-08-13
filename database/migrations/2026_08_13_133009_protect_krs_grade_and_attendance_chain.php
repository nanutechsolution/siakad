<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION 3 — Rantai paling kritis: krs -> krs_detail -> (nilai, presensi, log revisi).
 *
 * `krs` sebelumnya TIDAK punya deleted_at, sehingga setiap DELETE selalu hard-delete
 * dan CASCADE memusnahkan nilai + presensi. Migration ini:
 *   1. Menambahkan deleted_at pada krs & krs_detail (soft delete).
 *   2. Mengubah krs_detail, krs_detail_nilai, perkuliahan_absensi,
 *      akademik_grade_revision_logs dari CASCADE -> RESTRICT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('krs_detail', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('krs_detail', function (Blueprint $table) {
            $table->dropForeign(['krs_id']);
            $table->foreign('krs_id')->references('id')->on('krs')->onDelete('restrict');
        });

        Schema::table('krs_detail_nilai', function (Blueprint $table) {
            $table->dropForeign(['krs_detail_id']);
            $table->foreign('krs_detail_id')->references('id')->on('krs_detail')->onDelete('restrict');
        });

        Schema::table('perkuliahan_absensi', function (Blueprint $table) {
            $table->dropForeign(['krs_detail_id']);
            $table->foreign('krs_detail_id')->references('id')->on('krs_detail')->onDelete('restrict');
        });

        Schema::table('akademik_grade_revision_logs', function (Blueprint $table) {
            $table->dropForeign(['krs_detail_id']);
            $table->foreign('krs_detail_id')->references('id')->on('krs_detail')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('akademik_grade_revision_logs', function (Blueprint $table) {
            $table->dropForeign(['krs_detail_id']);
            $table->foreign('krs_detail_id')->references('id')->on('krs_detail')->onDelete('cascade');
        });

        Schema::table('perkuliahan_absensi', function (Blueprint $table) {
            $table->dropForeign(['krs_detail_id']);
            $table->foreign('krs_detail_id')->references('id')->on('krs_detail')->onDelete('cascade');
        });

        Schema::table('krs_detail_nilai', function (Blueprint $table) {
            $table->dropForeign(['krs_detail_id']);
            $table->foreign('krs_detail_id')->references('id')->on('krs_detail')->onDelete('cascade');
        });

        Schema::table('krs_detail', function (Blueprint $table) {
            $table->dropForeign(['krs_id']);
            $table->foreign('krs_id')->references('id')->on('krs')->onDelete('cascade');
        });

        Schema::table('krs_detail', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('krs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
