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
        Schema::create('riwayat_prodi_mahasiswas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('mahasiswa_id', 36);
            $table->unsignedBigInteger('prodi_id')->index('riwayat_prodi_mahasiswas_prodi_id_foreign');
            $table->string('nomor_sk')->nullable();
            $table->date('tanggal_berlaku');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_prodi_mahasiswas');
    }
};
