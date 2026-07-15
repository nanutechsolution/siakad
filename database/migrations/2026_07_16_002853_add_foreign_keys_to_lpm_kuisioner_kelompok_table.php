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
        Schema::table('lpm_kuisioner_kelompok', function (Blueprint $table) {
            $table->foreign(['tahun_akademik_id'])->references(['id'])->on('ref_tahun_akademik')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lpm_kuisioner_kelompok', function (Blueprint $table) {
            $table->dropForeign('lpm_kuisioner_kelompok_tahun_akademik_id_foreign');
        });
    }
};
