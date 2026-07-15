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
        Schema::create('lpm_edom_jawaban', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('jadwal_kuliah_id', 36);
            $table->unsignedBigInteger('pertanyaan_id')->index('idx_edom_pertanyaan');
            $table->char('dosen_id', 36)->nullable();
            $table->string('jawaban_nilai')->nullable()->comment('Bisa skor angka atau isian teks/esai');
            $table->timestamps();

            $table->index(['dosen_id', 'jadwal_kuliah_id', 'pertanyaan_id'], 'idx_edom_jawaban_agregasi');
            $table->index(['pertanyaan_id'], 'lpm_edom_jawaban_pertanyaan_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_edom_jawaban');
    }
};
