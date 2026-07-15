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
        Schema::table('lppm_skemas', function (Blueprint $table) {
            $table->foreign(['jenis_skema_id'])->references(['id'])->on('lppm_ref_jenis_skemas')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['tahun_akademik_id'])->references(['id'])->on('ref_tahun_akademik')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lppm_skemas', function (Blueprint $table) {
            $table->dropForeign('lppm_skemas_jenis_skema_id_foreign');
            $table->dropForeign('lppm_skemas_tahun_akademik_id_foreign');
        });
    }
};
