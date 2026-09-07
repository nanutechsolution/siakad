<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_pengampus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tahun_akademik_id');
            $table->unsignedBigInteger('mata_kuliah_id');
            $table->unsignedBigInteger('kelas_id');
            $table->char('dosen_id', 36);
            $table->boolean('is_koordinator')->default(0);
            $table->boolean('is_penilai')->default(1);
            $table->timestamps();

            // Cegah duplikasi penugasan dosen yang sama pada kelas & MK yang sama
            $table->unique(
                ['tahun_akademik_id', 'mata_kuliah_id', 'kelas_id', 'dosen_id'],
                'dosen_pengampu_unique_assignment'
            );

            $table->foreign('tahun_akademik_id')->references('id')->on('ref_tahun_akademik')->onDelete('cascade');
            $table->foreign('mata_kuliah_id')->references('id')->on('master_mata_kuliahs')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('dosen_id')->references('id')->on('trx_dosen')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen_pengampus');
    }
};
