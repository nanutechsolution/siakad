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
        Schema::table('lppm_usulans', function (Blueprint $table) {
            $table->foreign(['dosen_ketua_id'])->references(['id'])->on('trx_dosen')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['skema_id'])->references(['id'])->on('lppm_skemas')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lppm_usulans', function (Blueprint $table) {
            $table->dropForeign('lppm_usulans_dosen_ketua_id_foreign');
            $table->dropForeign('lppm_usulans_skema_id_foreign');
        });
    }
};
