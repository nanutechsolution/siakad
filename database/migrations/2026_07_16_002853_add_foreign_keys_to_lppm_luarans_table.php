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
        Schema::table('lppm_luarans', function (Blueprint $table) {
            $table->foreign(['dosen_id'])->references(['id'])->on('trx_dosen')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['jenis_luaran_id'])->references(['id'])->on('lppm_ref_jenis_luarans')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['verified_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lppm_luarans', function (Blueprint $table) {
            $table->dropForeign('lppm_luarans_dosen_id_foreign');
            $table->dropForeign('lppm_luarans_jenis_luaran_id_foreign');
            $table->dropForeign('lppm_luarans_verified_by_foreign');
        });
    }
};
