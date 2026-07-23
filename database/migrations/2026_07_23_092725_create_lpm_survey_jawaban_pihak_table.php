<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOTE: lpm_survey_jawaban (tunggal, sudah ada) terkunci ke mahasiswa_id —
 * dipakai apa adanya untuk Kepuasan Mahasiswa. Tabel BARU ini melengkapi
 * gap untuk responden non-mahasiswa (Dosen/Tendik/Alumni/Pengguna Lulusan)
 * dengan pola yang sama (flat, tanpa tabel "responden" perantara) supaya
 * konsisten dengan desain yang sudah ada, bukan mendupilkasi konsep baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_survey_jawaban_pihak', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_responden', ['DOSEN', 'TENDIK', 'ALUMNI', 'PENGGUNA_LULUSAN']);
            // person_id diisi untuk responden internal (dosen/tendik yang datanya
            // ada di ref_person). Alumni/pengguna lulusan yang belum tentu
            // tercatat sebagai ref_person memakai nama_eksternal/instansi_eksternal.
            $table->foreignId('person_id')->nullable()->constrained('ref_person')->nullOnDelete();
            $table->string('nama_eksternal', 255)->nullable();
            $table->string('instansi_eksternal', 255)->nullable();
            $table->foreignId('pertanyaan_id')->constrained('lpm_kuisioner_pertanyaan')->cascadeOnDelete();
            $table->foreignId('tahun_akademik_id')->constrained('ref_tahun_akademik')->cascadeOnDelete();
            $table->text('jawaban_nilai')->nullable();
            $table->timestamps();

            $table->unique(
                ['person_id', 'pertanyaan_id', 'tahun_akademik_id'],
                'unique_survey_pihak_person_ta'
            );
            $table->index(['jenis_responden', 'tahun_akademik_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_survey_jawaban_pihak');
    }
};
