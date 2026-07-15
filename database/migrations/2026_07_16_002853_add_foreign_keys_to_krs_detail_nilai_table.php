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
        Schema::table('krs_detail_nilai', function (Blueprint $table) {
            $table->foreign(['komponen_id'])->references(['id'])->on('ref_komponen_nilai')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['krs_detail_id'])->references(['id'])->on('krs_detail')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('krs_detail_nilai', function (Blueprint $table) {
            $table->dropForeign('krs_detail_nilai_komponen_id_foreign');
            $table->dropForeign('krs_detail_nilai_krs_detail_id_foreign');
        });
    }
};
