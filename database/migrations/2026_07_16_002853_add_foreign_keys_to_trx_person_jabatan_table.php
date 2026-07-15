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
        Schema::table('trx_person_jabatan', function (Blueprint $table) {
            $table->foreign(['fakultas_id'])->references(['id'])->on('ref_fakultas')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['jabatan_id'])->references(['id'])->on('ref_jabatan')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['person_id'])->references(['id'])->on('ref_person')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['prodi_id'])->references(['id'])->on('ref_prodi')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trx_person_jabatan', function (Blueprint $table) {
            $table->dropForeign('trx_person_jabatan_fakultas_id_foreign');
            $table->dropForeign('trx_person_jabatan_jabatan_id_foreign');
            $table->dropForeign('trx_person_jabatan_person_id_foreign');
            $table->dropForeign('trx_person_jabatan_prodi_id_foreign');
        });
    }
};
