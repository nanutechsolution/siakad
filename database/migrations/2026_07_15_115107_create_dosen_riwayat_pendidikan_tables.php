<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_riwayat_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->char('dosen_id', 36);
            $table->enum('jenjang', ['D3', 'D4', 'S1', 'S2', 'S3', 'PROFESI'])->nullable();
            $table->string('nama_institusi');
            $table->string('program_studi')->nullable();
            $table->year('tahun_lulus')->nullable();
            $table->string('judul_tugas_akhir')->nullable();
            $table->string('file_ijazah_path')->nullable();
            $table->timestamps();

            $table->index('dosen_id');
            $table->foreign('dosen_id')
                ->references('id')->on('trx_dosen')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen_riwayat_pendidikan');
    }
};