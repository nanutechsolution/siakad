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
        Schema::table('lpm_edom_progress', function (Blueprint $table) {
            $table->foreign(['dosen_id'])->references(['id'])->on('trx_dosen')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['jadwal_kuliah_id'])->references(['id'])->on('jadwal_kuliah')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['mahasiswa_id'])->references(['id'])->on('mahasiswas')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lpm_edom_progress', function (Blueprint $table) {
            $table->dropForeign('lpm_edom_progress_dosen_id_foreign');
            $table->dropForeign('lpm_edom_progress_jadwal_kuliah_id_foreign');
            $table->dropForeign('lpm_edom_progress_mahasiswa_id_foreign');
        });
    }
};
