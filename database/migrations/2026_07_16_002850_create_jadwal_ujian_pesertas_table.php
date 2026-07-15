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
        Schema::create('jadwal_ujian_pesertas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('jadwal_ujian_id', 36);
            $table->unsignedBigInteger('krs_detail_id')->index('jadwal_ujian_pesertas_krs_detail_id_foreign');
            $table->char('status_kehadiran', 1)->default('A');
            $table->string('nomor_kursi', 10)->nullable();
            $table->dateTime('waktu_check_in')->nullable();
            $table->text('catatan_pelanggaran')->nullable();
            $table->timestamps();

            $table->unique(['jadwal_ujian_id', 'krs_detail_id'], 'jup_ujian_krsd_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujian_pesertas');
    }
};
