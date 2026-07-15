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
        Schema::table('keuangan_skema_tarif', function (Blueprint $table) {
            $table->foreign(['angkatan_id'])->references(['id_tahun'])->on('ref_angkatan')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['prodi_id'])->references(['id'])->on('ref_prodi')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['program_kelas_id'])->references(['id'])->on('ref_program')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan_skema_tarif', function (Blueprint $table) {
            $table->dropForeign('keuangan_skema_tarif_angkatan_id_foreign');
            $table->dropForeign('keuangan_skema_tarif_prodi_id_foreign');
            $table->dropForeign('keuangan_skema_tarif_program_kelas_id_foreign');
        });
    }
};
