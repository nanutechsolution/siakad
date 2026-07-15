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
        Schema::table('keuangan_beasiswa_details', function (Blueprint $table) {
            $table->foreign(['beasiswa_id'])->references(['id'])->on('keuangan_master_beasiswas')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['komponen_biaya_id'])->references(['id'])->on('keuangan_komponen_biaya')->onUpdate('no action')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan_beasiswa_details', function (Blueprint $table) {
            $table->dropForeign('keuangan_beasiswa_details_beasiswa_id_foreign');
            $table->dropForeign('keuangan_beasiswa_details_komponen_biaya_id_foreign');
        });
    }
};
