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
        Schema::create('perkuliahan_sesi', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('jadwal_kuliah_id', 36);
            $table->integer('pertemuan_ke');
            $table->dateTime('waktu_mulai_rencana');
            $table->dateTime('waktu_mulai_realisasi')->nullable();
            $table->dateTime('waktu_selesai_realisasi')->nullable();
            $table->text('materi_kuliah')->nullable();
            $table->text('catatan_dosen')->nullable();
            $table->string('token_sesi', 10)->nullable()->index();
            $table->timestamp('token_generated_at')->nullable();
            $table->string('metode_validasi', 20)->default('QR');
            $table->enum('status_sesi', ['terjadwal', 'dibuka', 'selesai', 'dibatalkan'])->default('terjadwal')->index();
            $table->timestamps();

            $table->index(['jadwal_kuliah_id', 'pertemuan_ke']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perkuliahan_sesi');
    }
};
