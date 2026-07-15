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
        Schema::create('mahasiswa_kelas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('mahasiswa_id', 36)->index('mahasiswa_kelas_mahasiswa_id_foreign');
            $table->unsignedBigInteger('kelas_id')->index('mahasiswa_kelas_kelas_id_foreign');
            $table->date('tanggal_masuk')->nullable();
            $table->date('tanggal_keluar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa_kelas');
    }
};
