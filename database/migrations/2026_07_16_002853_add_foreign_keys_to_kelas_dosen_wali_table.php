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
        Schema::table('kelas_dosen_wali', function (Blueprint $table) {
            $table->foreign(['dosen_id'])->references(['id'])->on('trx_dosen')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['kelas_id'])->references(['id'])->on('kelas')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas_dosen_wali', function (Blueprint $table) {
            $table->dropForeign('kelas_dosen_wali_dosen_id_foreign');
            $table->dropForeign('kelas_dosen_wali_kelas_id_foreign');
        });
    }
};
