<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dosen_riwayat_pendidikan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('dosen_id', 36)->index();
            $table->enum('jenjang', ['D3', 'D4', 'S1', 'S2', 'S3', 'PROFESI'])->nullable();
            $table->string('nama_institusi');
            $table->string('program_studi')->nullable();
            $table->year('tahun_lulus')->nullable();
            $table->string('judul_tugas_akhir')->nullable();
            $table->string('file_ijazah_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_riwayat_pendidikan');
    }
};
