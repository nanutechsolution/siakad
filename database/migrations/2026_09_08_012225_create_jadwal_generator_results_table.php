<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_generator_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');

            // Komponen Unit Jadwal
            $table->unsignedBigInteger('mata_kuliah_id');
            $table->unsignedBigInteger('kelas_id');
            $table->json('dosen_pengampu_ids')->comment('Array dari ID tabel dosen_pengampus');
            $table->integer('sks_real')->comment('SKS dari kurikulum_mata_kuliah');

            // Hasil Algoritma (Bisa NULL jika gagal plotting)
            $table->string('hari', 10)->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->unsignedBigInteger('ruang_id')->nullable();
            $table->integer('estimasi_kapasitas_dibutuhkan')->default(0);

            // Indikator Engine
            $table->boolean('is_success')->default(false);
            $table->string('failure_reason', 255)->nullable();
            $table->integer('optimization_score')->default(0);

            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('jadwal_generator_batches')->onDelete('cascade');
            $table->foreign('mata_kuliah_id')->references('id')->on('master_mata_kuliahs')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('ruang_id')->references('id')->on('ref_ruang')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_generator_results');
    }
};
