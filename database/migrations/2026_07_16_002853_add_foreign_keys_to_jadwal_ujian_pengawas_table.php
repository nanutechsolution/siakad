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
        Schema::table('jadwal_ujian_pengawas', function (Blueprint $table) {
            $table->foreign(['jadwal_ujian_id'])->references(['id'])->on('jadwal_ujians')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['person_id'])->references(['id'])->on('ref_person')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_ujian_pengawas', function (Blueprint $table) {
            $table->dropForeign('jadwal_ujian_pengawas_jadwal_ujian_id_foreign');
            $table->dropForeign('jadwal_ujian_pengawas_person_id_foreign');
        });
    }
};
