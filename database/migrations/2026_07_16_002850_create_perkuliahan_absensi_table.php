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
        Schema::create('perkuliahan_absensi', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('perkuliahan_sesi_id', 36)->index('perkuliahan_absensi_perkuliahan_sesi_id_foreign');
            $table->unsignedBigInteger('krs_detail_id');
            $table->char('status_kehadiran', 1)->default('A')->index();
            $table->dateTime('waktu_check_in')->nullable();
            $table->json('bukti_validasi')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device_fingerprint', 64)->nullable();
            $table->boolean('is_flagged_duplikat')->default(false);
            $table->boolean('is_manual_update')->default(false);
            $table->string('modified_by_user_id', 36)->nullable();
            $table->string('alasan_perubahan')->nullable();
            $table->timestamps();

            $table->index(['krs_detail_id', 'status_kehadiran']);
            $table->index(['perkuliahan_sesi_id', 'device_fingerprint']);
            $table->index(['perkuliahan_sesi_id', 'ip_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perkuliahan_absensi');
    }
};
