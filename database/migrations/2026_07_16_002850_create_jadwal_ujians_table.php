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
        Schema::create('jadwal_ujians', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('jadwal_kuliah_id', 36)->index('jadwal_ujians_jadwal_kuliah_id_foreign');
            $table->enum('jenis_ujian', ['UTS', 'UAS', 'SUSULAN', 'LAINNYA']);
            $table->date('tanggal_ujian');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->unsignedBigInteger('ruang_id')->nullable()->index('jadwal_ujians_ruang_id_foreign');
            $table->string('metode_ujian', 50)->default('TERTULIS');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujians');
    }
};
