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
        Schema::table('jadwal_kuliah_dosen', function (Blueprint $table) {
            $table->foreign(['dosen_id'])->references(['id'])->on('trx_dosen')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['jadwal_kuliah_id'])->references(['id'])->on('jadwal_kuliah')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_kuliah_dosen', function (Blueprint $table) {
            $table->dropForeign('jadwal_kuliah_dosen_dosen_id_foreign');
            $table->dropForeign('jadwal_kuliah_dosen_jadwal_kuliah_id_foreign');
        });
    }
};
