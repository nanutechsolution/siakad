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
        Schema::table('riwayat_prodi_mahasiswas', function (Blueprint $table) {
            $table->foreign(['mahasiswa_id'])->references(['id'])->on('mahasiswas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['prodi_id'])->references(['id'])->on('ref_prodi')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_prodi_mahasiswas', function (Blueprint $table) {
            $table->dropForeign('riwayat_prodi_mahasiswas_mahasiswa_id_foreign');
            $table->dropForeign('riwayat_prodi_mahasiswas_prodi_id_foreign');
        });
    }
};
