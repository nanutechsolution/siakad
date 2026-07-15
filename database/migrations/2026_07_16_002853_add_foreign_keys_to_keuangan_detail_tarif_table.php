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
        Schema::table('keuangan_detail_tarif', function (Blueprint $table) {
            $table->foreign(['komponen_biaya_id'])->references(['id'])->on('keuangan_komponen_biaya')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['skema_tarif_id'])->references(['id'])->on('keuangan_skema_tarif')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan_detail_tarif', function (Blueprint $table) {
            $table->dropForeign('keuangan_detail_tarif_komponen_biaya_id_foreign');
            $table->dropForeign('keuangan_detail_tarif_skema_tarif_id_foreign');
        });
    }
};
