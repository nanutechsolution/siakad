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
        Schema::table('akademik_ekuivalensi', function (Blueprint $table) {
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['mk_asal_id'])->references(['id'])->on('master_mata_kuliahs')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['mk_tujuan_id'])->references(['id'])->on('master_mata_kuliahs')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['prodi_id'])->references(['id'])->on('ref_prodi')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('akademik_ekuivalensi', function (Blueprint $table) {
            $table->dropForeign('akademik_ekuivalensi_created_by_foreign');
            $table->dropForeign('akademik_ekuivalensi_mk_asal_id_foreign');
            $table->dropForeign('akademik_ekuivalensi_mk_tujuan_id_foreign');
            $table->dropForeign('akademik_ekuivalensi_prodi_id_foreign');
        });
    }
};
