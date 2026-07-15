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
        Schema::table('dosen_dokumen', function (Blueprint $table) {
            $table->foreign(['dosen_id'])->references(['id'])->on('trx_dosen')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ref_dokumen_dosen_id'])->references(['id'])->on('ref_dokumen_dosen')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['reviewed_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dosen_dokumen', function (Blueprint $table) {
            $table->dropForeign('dosen_dokumen_dosen_id_foreign');
            $table->dropForeign('dosen_dokumen_ref_dokumen_dosen_id_foreign');
            $table->dropForeign('dosen_dokumen_reviewed_by_foreign');
        });
    }
};
