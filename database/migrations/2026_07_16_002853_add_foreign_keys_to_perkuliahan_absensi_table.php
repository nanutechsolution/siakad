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
        Schema::table('perkuliahan_absensi', function (Blueprint $table) {
            $table->foreign(['krs_detail_id'])->references(['id'])->on('krs_detail')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['perkuliahan_sesi_id'])->references(['id'])->on('perkuliahan_sesi')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perkuliahan_absensi', function (Blueprint $table) {
            $table->dropForeign('perkuliahan_absensi_krs_detail_id_foreign');
            $table->dropForeign('perkuliahan_absensi_perkuliahan_sesi_id_foreign');
        });
    }
};
