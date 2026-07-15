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
        Schema::table('pembayaran_mahasiswas', function (Blueprint $table) {
            $table->foreign(['status_verifikasi_id'])->references(['id'])->on('ref_status_verifikasi_pembayaran')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['tagihan_id'])->references(['id'])->on('tagihan_mahasiswas')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['verified_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran_mahasiswas', function (Blueprint $table) {
            $table->dropForeign('pembayaran_mahasiswas_status_verifikasi_id_foreign');
            $table->dropForeign('pembayaran_mahasiswas_tagihan_id_foreign');
            $table->dropForeign('pembayaran_mahasiswas_verified_by_foreign');
        });
    }
};
