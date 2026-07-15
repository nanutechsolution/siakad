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
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->foreign(['angkatan_id'])->references(['id_tahun'])->on('ref_angkatan')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['kurikulum_id'])->references(['id'])->on('master_kurikulums')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['person_id'])->references(['id'])->on('ref_person')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['prodi_id'])->references(['id'])->on('ref_prodi')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['program_id'])->references(['id'])->on('ref_program')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropForeign('mahasiswas_angkatan_id_foreign');
            $table->dropForeign('mahasiswas_kurikulum_id_foreign');
            $table->dropForeign('mahasiswas_person_id_foreign');
            $table->dropForeign('mahasiswas_prodi_id_foreign');
            $table->dropForeign('mahasiswas_program_id_foreign');
        });
    }
};
