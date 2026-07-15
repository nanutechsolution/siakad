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
        Schema::table('lpm_edom_jawaban', function (Blueprint $table) {
            $table->foreign(['dosen_id'])->references(['id'])->on('trx_dosen')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['pertanyaan_id'])->references(['id'])->on('lpm_kuisioner_pertanyaan')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lpm_edom_jawaban', function (Blueprint $table) {
            $table->dropForeign('lpm_edom_jawaban_dosen_id_foreign');
            $table->dropForeign('lpm_edom_jawaban_pertanyaan_id_foreign');
        });
    }
};
