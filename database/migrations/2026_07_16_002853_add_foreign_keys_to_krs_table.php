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
        Schema::table('krs', function (Blueprint $table) {
            $table->foreign(['disetujui_oleh'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['dispensasi_id'])->references(['id'])->on('dispensasi_akademiks')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['ditolak_oleh'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['dosen_wali_id'])->references(['id'])->on('trx_dosen')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['financial_override_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['kelas_id'])->references(['id'])->on('kelas')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['mahasiswa_id'])->references(['id'])->on('mahasiswas')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['tahun_akademik_id'])->references(['id'])->on('ref_tahun_akademik')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->dropForeign('krs_disetujui_oleh_foreign');
            $table->dropForeign('krs_dispensasi_id_foreign');
            $table->dropForeign('krs_ditolak_oleh_foreign');
            $table->dropForeign('krs_dosen_wali_id_foreign');
            $table->dropForeign('krs_financial_override_by_foreign');
            $table->dropForeign('krs_kelas_id_foreign');
            $table->dropForeign('krs_mahasiswa_id_foreign');
            $table->dropForeign('krs_tahun_akademik_id_foreign');
        });
    }
};
