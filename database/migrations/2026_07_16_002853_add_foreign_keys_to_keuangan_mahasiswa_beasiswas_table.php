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
        Schema::table('keuangan_mahasiswa_beasiswas', function (Blueprint $table) {
            $table->foreign(['beasiswa_id'])->references(['id'])->on('keuangan_master_beasiswas')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['mahasiswa_id'])->references(['id'])->on('mahasiswas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['tahun_akademik_akhir_id'])->references(['id'])->on('ref_tahun_akademik')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['tahun_akademik_mulai_id'])->references(['id'])->on('ref_tahun_akademik')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan_mahasiswa_beasiswas', function (Blueprint $table) {
            $table->dropForeign('keuangan_mahasiswa_beasiswas_beasiswa_id_foreign');
            $table->dropForeign('keuangan_mahasiswa_beasiswas_mahasiswa_id_foreign');
            $table->dropForeign('keuangan_mahasiswa_beasiswas_tahun_akademik_akhir_id_foreign');
            $table->dropForeign('keuangan_mahasiswa_beasiswas_tahun_akademik_mulai_id_foreign');
        });
    }
};
