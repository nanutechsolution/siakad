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
        Schema::table('akademik_transkrip', function (Blueprint $table) {
            $table->foreign(['krs_detail_id'])->references(['id'])->on('krs_detail')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['mahasiswa_id'])->references(['id'])->on('mahasiswas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['mata_kuliah_id'])->references(['id'])->on('master_mata_kuliahs')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akademik_transkrip', function (Blueprint $table) {
            $table->dropForeign('akademik_transkrip_krs_detail_id_foreign');
            $table->dropForeign('akademik_transkrip_mahasiswa_id_foreign');
            $table->dropForeign('akademik_transkrip_mata_kuliah_id_foreign');
        });
    }
};
