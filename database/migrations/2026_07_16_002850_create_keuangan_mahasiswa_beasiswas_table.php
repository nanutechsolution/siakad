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
        Schema::create('keuangan_mahasiswa_beasiswas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('mahasiswa_id', 36);
            $table->unsignedBigInteger('beasiswa_id')->index('keuangan_mahasiswa_beasiswas_beasiswa_id_foreign');
            $table->unsignedBigInteger('tahun_akademik_mulai_id')->index('keuangan_mahasiswa_beasiswas_tahun_akademik_mulai_id_foreign');
            $table->unsignedBigInteger('tahun_akademik_akhir_id')->nullable()->index('keuangan_mahasiswa_beasiswas_tahun_akademik_akhir_id_foreign');
            $table->string('nomor_sk', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['mahasiswa_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_mahasiswa_beasiswas');
    }
};
