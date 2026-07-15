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
        Schema::create('riwayat_status_mahasiswas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('mahasiswa_id', 36);
            $table->unsignedBigInteger('tahun_akademik_id')->index('riwayat_status_mahasiswas_tahun_akademik_id_foreign');
            $table->char('status_kuliah', 1)->default('A')->index();
            $table->decimal('ips', 4)->default(0);
            $table->decimal('ipk', 4)->default(0);
            $table->integer('sks_semester')->default(0);
            $table->integer('sks_total')->default(0);
            $table->string('nomor_sk')->nullable();
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'tahun_akademik_id'], 'unique_status_per_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_mahasiswas');
    }
};
