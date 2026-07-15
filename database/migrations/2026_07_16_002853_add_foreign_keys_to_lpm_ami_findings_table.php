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
        Schema::table('lpm_ami_findings', function (Blueprint $table) {
            $table->foreign(['periode_id'])->references(['id'])->on('lpm_ami_periodes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['prodi_id'])->references(['id'])->on('ref_prodi')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['standar_id'])->references(['id'])->on('lpm_standars')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lpm_ami_findings', function (Blueprint $table) {
            $table->dropForeign('lpm_ami_findings_periode_id_foreign');
            $table->dropForeign('lpm_ami_findings_prodi_id_foreign');
            $table->dropForeign('lpm_ami_findings_standar_id_foreign');
        });
    }
};
