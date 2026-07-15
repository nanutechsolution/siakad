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
        Schema::table('krs_detail', function (Blueprint $table) {
            $table->foreign(['ekuivalensi_id'])->references(['id'])->on('akademik_ekuivalensi')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['jadwal_kuliah_id'])->references(['id'])->on('jadwal_kuliah')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['krs_id'])->references(['id'])->on('krs')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['mata_kuliah_id'])->references(['id'])->on('master_mata_kuliahs')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('krs_detail', function (Blueprint $table) {
            $table->dropForeign('krs_detail_ekuivalensi_id_foreign');
            $table->dropForeign('krs_detail_jadwal_kuliah_id_foreign');
            $table->dropForeign('krs_detail_krs_id_foreign');
            $table->dropForeign('krs_detail_mata_kuliah_id_foreign');
        });
    }
};
