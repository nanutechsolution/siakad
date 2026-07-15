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
        Schema::table('ref_prodi', function (Blueprint $table) {
            $table->foreign(['fakultas_id'])->references(['id'])->on('ref_fakultas')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ref_prodi', function (Blueprint $table) {
            $table->dropForeign('ref_prodi_fakultas_id_foreign');
        });
    }
};
