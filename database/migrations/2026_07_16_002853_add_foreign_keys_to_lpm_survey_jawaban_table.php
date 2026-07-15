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
        Schema::table('lpm_survey_jawaban', function (Blueprint $table) {
            $table->foreign(['mahasiswa_id'])->references(['id'])->on('mahasiswas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['pertanyaan_id'])->references(['id'])->on('lpm_kuisioner_pertanyaan')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['tahun_akademik_id'])->references(['id'])->on('ref_tahun_akademik')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lpm_survey_jawaban', function (Blueprint $table) {
            $table->dropForeign('lpm_survey_jawaban_mahasiswa_id_foreign');
            $table->dropForeign('lpm_survey_jawaban_pertanyaan_id_foreign');
            $table->dropForeign('lpm_survey_jawaban_tahun_akademik_id_foreign');
        });
    }
};
