<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa_biodata', function (Blueprint $table) {
            $table->id();
            $table->char('mahasiswa_id', 36);

            // Alamat
            $table->text('alamat_ktp')->nullable();
            $table->text('alamat_domisili')->nullable();
            $table->string('kode_pos', 10)->nullable();

            // Data Ayah
            $table->string('nama_ayah')->nullable();
            $table->string('nik_ayah', 20)->nullable();
            $table->string('pendidikan_ayah', 20)->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('penghasilan_ayah', 30)->nullable();

            // Data Ibu
            $table->string('nama_ibu')->nullable();
            $table->string('nik_ibu', 20)->nullable();
            $table->string('pendidikan_ibu', 20)->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->string('penghasilan_ibu', 30)->nullable();

            // Wali (opsional, jika bukan orang tua kandung)
            $table->string('nama_wali')->nullable();
            $table->string('hubungan_wali', 50)->nullable();
            $table->string('pekerjaan_wali')->nullable();
            $table->string('no_hp_wali', 20)->nullable();

            // Data pelengkap umum SIAKAD
            $table->string('agama', 20)->nullable();
            $table->string('status_pernikahan', 20)->nullable();
            $table->unsignedInteger('anak_ke')->nullable();
            $table->unsignedInteger('jumlah_saudara')->nullable();
            $table->string('no_kip', 50)->nullable();

            $table->timestamps();

            $table->unique('mahasiswa_id');
            $table->foreign('mahasiswa_id')
                ->references('id')->on('mahasiswas')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_biodata');
    }
};