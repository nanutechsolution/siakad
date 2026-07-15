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
        Schema::table('jadwal_kuliah', function (Blueprint $table) {
            $table->foreign(['kelas_id'])->references(['id'])->on('kelas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['kurikulum_id'])->references(['id'])->on('master_kurikulums')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['mata_kuliah_id'])->references(['id'])->on('master_mata_kuliahs')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ruang_id'])->references(['id'])->on('ref_ruang')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['tahun_akademik_id'])->references(['id'])->on('ref_tahun_akademik')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_kuliah', function (Blueprint $table) {
            $table->dropForeign('jadwal_kuliah_kelas_id_foreign');
            $table->dropForeign('jadwal_kuliah_kurikulum_id_foreign');
            $table->dropForeign('jadwal_kuliah_mata_kuliah_id_foreign');
            $table->dropForeign('jadwal_kuliah_ruang_id_foreign');
            $table->dropForeign('jadwal_kuliah_tahun_akademik_id_foreign');
        });
    }
};
