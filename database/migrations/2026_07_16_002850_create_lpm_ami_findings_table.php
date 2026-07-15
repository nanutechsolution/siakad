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
        Schema::create('lpm_ami_findings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('periode_id')->index('lpm_ami_findings_periode_id_foreign');
            $table->unsignedBigInteger('prodi_id')->index('lpm_ami_findings_prodi_id_foreign');
            $table->string('jenis_temuan', 20)->default('OBSERVASI')->comment('MAYOR, MINOR, OBSERVASI');
            $table->unsignedBigInteger('standar_id')->index('lpm_ami_findings_standar_id_foreign');
            $table->string('auditor_name');
            $table->enum('klasifikasi', ['OB', 'KTS_MINOR', 'KTS_MAYOR']);
            $table->text('deskripsi_temuan');
            $table->text('rekomendasi')->nullable();
            $table->text('akar_masalah')->nullable();
            $table->text('rencana_tindak_lanjut')->nullable();
            $table->date('deadline_perbaikan')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->enum('status_workflow', ['OPEN', 'ACTION_PLAN', 'VERIFICATION', 'CLOSED'])->default('OPEN');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_ami_findings');
    }
};
