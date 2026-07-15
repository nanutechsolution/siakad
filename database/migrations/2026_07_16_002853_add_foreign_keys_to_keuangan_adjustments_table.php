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
        Schema::table('keuangan_adjustments', function (Blueprint $table) {
            $table->foreign(['adjustment_pembalik_id'])->references(['id'])->on('keuangan_adjustments')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['diajukan_oleh'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['dibatalkan_oleh'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['disetujui_oleh'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['tagihan_id'])->references(['id'])->on('tagihan_mahasiswas')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan_adjustments', function (Blueprint $table) {
            $table->dropForeign('keuangan_adjustments_adjustment_pembalik_id_foreign');
            $table->dropForeign('keuangan_adjustments_created_by_foreign');
            $table->dropForeign('keuangan_adjustments_diajukan_oleh_foreign');
            $table->dropForeign('keuangan_adjustments_dibatalkan_oleh_foreign');
            $table->dropForeign('keuangan_adjustments_disetujui_oleh_foreign');
            $table->dropForeign('keuangan_adjustments_tagihan_id_foreign');
        });
    }
};
