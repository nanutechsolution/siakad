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
        Schema::table('jadwal_ujian_pesertas', function (Blueprint $table) {
            $table->foreign(['jadwal_ujian_id'])->references(['id'])->on('jadwal_ujians')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['krs_detail_id'])->references(['id'])->on('krs_detail')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_ujian_pesertas', function (Blueprint $table) {
            $table->dropForeign('jadwal_ujian_pesertas_jadwal_ujian_id_foreign');
            $table->dropForeign('jadwal_ujian_pesertas_krs_detail_id_foreign');
        });
    }
};
