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
        Schema::table('tagihan_mahasiswas_details', function (Blueprint $table) {
            $table->foreign(['komponen_biaya_id'])->references(['id'])->on('keuangan_komponen_biaya')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['tagihan_id'])->references(['id'])->on('tagihan_mahasiswas')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan_mahasiswas_details', function (Blueprint $table) {
            $table->dropForeign('tagihan_mahasiswas_details_komponen_biaya_id_foreign');
            $table->dropForeign('tagihan_mahasiswas_details_tagihan_id_foreign');
        });
    }
};
