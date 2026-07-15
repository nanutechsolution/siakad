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
        Schema::create('jadwal_kuliah', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('tahun_akademik_id')->index('jadwal_kuliah_tahun_akademik_id_foreign');
            $table->unsignedBigInteger('kurikulum_id')->nullable()->index('jadwal_kuliah_kurikulum_id_foreign');
            $table->unsignedBigInteger('mata_kuliah_id')->index('jadwal_kuliah_mata_kuliah_id_foreign');
            $table->unsignedBigInteger('kelas_id')->index('jadwal_kuliah_kelas_id_foreign');
            $table->string('hari', 10)->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->unsignedBigInteger('ruang_id')->nullable()->index('jadwal_kuliah_ruang_id_foreign');
            $table->integer('kuota_kelas')->default(40);
            $table->integer('isi_kelas')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kuliah');
    }
};
