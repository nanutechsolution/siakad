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
        Schema::table('dosen_biodata', function (Blueprint $table) {
            $table->foreign(['dosen_id'])->references(['id'])->on('trx_dosen')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dosen_biodata', function (Blueprint $table) {
            $table->dropForeign('dosen_biodata_dosen_id_foreign');
        });
    }
};
