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
        Schema::create('keuangan_adjustments', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('nomor_adjustment', 50)->nullable()->unique();
            $table->char('tagihan_id', 36)->index('keuangan_adjustments_tagihan_id_foreign');
            $table->string('jenis_adjustment', 20);
            $table->decimal('nominal', 15);
            $table->text('keterangan')->nullable();
            $table->string('status', 20)->default('DRAFT');
            $table->char('created_by', 36)->nullable()->index('keuangan_adjustments_created_by_foreign');
            $table->char('diajukan_oleh', 36)->nullable()->index('keuangan_adjustments_diajukan_oleh_foreign');
            $table->timestamp('diajukan_at')->nullable();
            $table->char('disetujui_oleh', 36)->nullable()->index('keuangan_adjustments_disetujui_oleh_foreign');
            $table->timestamp('disetujui_at')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->timestamp('diposting_at')->nullable();
            $table->char('dibatalkan_oleh', 36)->nullable()->index('keuangan_adjustments_dibatalkan_oleh_foreign');
            $table->timestamp('dibatalkan_at')->nullable();
            $table->text('alasan_pembatalan')->nullable();
            $table->char('adjustment_pembalik_id', 36)->nullable()->index('keuangan_adjustments_adjustment_pembalik_id_foreign');
            $table->string('tindak_lanjut_kelebihan_bayar', 20)->default('TIDAK_ADA');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tagihan_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_adjustments');
    }
};
