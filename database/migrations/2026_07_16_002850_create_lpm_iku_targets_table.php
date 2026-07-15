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
        Schema::create('lpm_iku_targets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('indikator_id')->index('lpm_iku_targets_indikator_id_foreign');
            $table->unsignedBigInteger('prodi_id')->nullable();
            $table->integer('tahun');
            $table->decimal('target_nilai', 10);
            $table->decimal('capaian_nilai', 10)->default(0);
            $table->string('file_bukti_path')->nullable();
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'VALIDATED'])->default('DRAFT');
            $table->string('verified_by', 36)->nullable();
            $table->text('analisis_kendala')->nullable();
            $table->text('tindakan_koreksi')->nullable();
            $table->timestamps();

            $table->unique(['indikator_id', 'prodi_id', 'tahun'], 'unique_target_iku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_iku_targets');
    }
};
