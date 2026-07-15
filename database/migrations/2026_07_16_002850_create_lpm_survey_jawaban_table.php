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
        Schema::create('lpm_survey_jawaban', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('mahasiswa_id', 36);
            $table->unsignedBigInteger('pertanyaan_id')->index('lpm_survey_jawaban_pertanyaan_id_foreign');
            $table->unsignedBigInteger('tahun_akademik_id')->index('lpm_survey_jawaban_tahun_akademik_id_foreign');
            $table->text('jawaban_nilai')->nullable();
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'pertanyaan_id', 'tahun_akademik_id'], 'unique_survey_mhs_ta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_survey_jawaban');
    }
};
