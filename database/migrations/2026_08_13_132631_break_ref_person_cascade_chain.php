<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION 1 — Putuskan akar rantai cascade ref_person.
 *
 * ref_person -> mahasiswas dan ref_person -> trx_dosen sebelumnya ON DELETE CASCADE.
 * Ini adalah akar dari seluruh rantai kritis (transkrip, riwayat status, mahasiswa_kelas,
 * keuangan_saldos, academic_history_logs ikut terhapus jika ref_person dihapus).
 *
 * PENTING: sebelum menjalankan migration ini, pastikan tidak ada mahasiswas/trx_dosen
 * yatim (person_id yang sudah tidak ada di ref_person). Jika ada, RESTRICT akan
 * membuat migration ini tetap berhasil dibuat (constraint hanya berlaku ke depan),
 * tapi sebaiknya tetap dibersihkan dulu:
 *
 *   SELECT m.id FROM mahasiswas m LEFT JOIN ref_person p ON p.id = m.person_id WHERE p.id IS NULL;
 *   SELECT d.id FROM trx_dosen d LEFT JOIN ref_person p ON p.id = d.person_id WHERE p.id IS NULL;
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->foreign('person_id')
                ->references('id')->on('ref_person')
                ->onDelete('restrict');
        });

        Schema::table('trx_dosen', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->foreign('person_id')
                ->references('id')->on('ref_person')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->foreign('person_id')
                ->references('id')->on('ref_person')
                ->onDelete('cascade');
        });

        Schema::table('trx_dosen', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->foreign('person_id')
                ->references('id')->on('ref_person')
                ->onDelete('cascade');
        });
    }
};
