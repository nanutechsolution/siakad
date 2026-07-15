<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Upgrade tipe data sequence untuk mencegah error Numeric Out of Range
        Schema::table('ref_prodi', function (Blueprint $table) {
            $table->unsignedBigInteger('last_nim_seq')->change();
        });

        // 2. Tambahkan kategori pada kelompok kuesioner
        Schema::table('lpm_kuisioner_kelompok', function (Blueprint $table) {
            $table->string('kategori', 50)->default('EDOM')->after('nama_kelompok');
        });

        // 3. Modifikasi EDOM untuk mendukung Team Teaching
        Schema::table('lpm_edom_jawaban', function (Blueprint $table) {
            $table->char('dosen_id', 36)->nullable()->after('pertanyaan_id');
            $table->foreign('dosen_id')->references('id')->on('trx_dosen')->cascadeOnDelete();
            $table->unique(['krs_detail_id', 'pertanyaan_id', 'dosen_id'], 'unique_edom_jawaban_dosen');
        });

        Schema::table('lpm_edom_saran', function (Blueprint $table) {
            $table->char('dosen_id', 36)->nullable()->after('krs_detail_id');
            $table->foreign('dosen_id')->references('id')->on('trx_dosen')->cascadeOnDelete();
            $table->unique(['krs_detail_id', 'dosen_id'], 'unique_edom_saran_dosen');
        });

        // 4. Buat tabel khusus untuk Survey Umum (Fasilitas, Layanan, dll)
        Schema::create('lpm_survey_jawaban', function (Blueprint $table) {
            $table->id();
            $table->char('mahasiswa_id', 36);
            $table->unsignedBigInteger('pertanyaan_id');
            $table->unsignedBigInteger('tahun_akademik_id');
            $table->text('jawaban_nilai')->nullable();
            $table->timestamps();

            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->cascadeOnDelete();
            $table->foreign('pertanyaan_id')->references('id')->on('lpm_kuisioner_pertanyaan')->cascadeOnDelete();
            $table->foreign('tahun_akademik_id')->references('id')->on('ref_tahun_akademik')->cascadeOnDelete();

            $table->unique(['mahasiswa_id', 'pertanyaan_id', 'tahun_akademik_id'], 'unique_survey_mhs_ta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_survey_jawaban');
        
        Schema::table('lpm_edom_saran', function (Blueprint $table) {
            $table->dropForeign(['dosen_id']);
            $table->dropUnique('unique_edom_saran_dosen');
            $table->dropColumn('dosen_id');
        });

        Schema::table('lpm_edom_jawaban', function (Blueprint $table) {
            $table->dropForeign(['dosen_id']);
            $table->dropUnique('unique_edom_jawaban_dosen');
            $table->dropColumn('dosen_id');
        });

        Schema::table('lpm_kuisioner_kelompok', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};