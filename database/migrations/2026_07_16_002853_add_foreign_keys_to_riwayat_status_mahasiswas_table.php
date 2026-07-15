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
        Schema::table('riwayat_status_mahasiswas', function (Blueprint $table) {
            $table->foreign(['mahasiswa_id'])->references(['id'])->on('mahasiswas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['tahun_akademik_id'])->references(['id'])->on('ref_tahun_akademik')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_status_mahasiswas', function (Blueprint $table) {
            $table->dropForeign('riwayat_status_mahasiswas_mahasiswa_id_foreign');
            $table->dropForeign('riwayat_status_mahasiswas_tahun_akademik_id_foreign');
        });
    }
};
