<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keuangan_adjustments', function (Blueprint $table): void {
            $table->string('nomor_adjustment', 50)->nullable()->after('id');
            $table->string('status', 20)->default('DRAFT')->after('keterangan');
            $table->uuid('diajukan_oleh')->nullable()->after('created_by');
            $table->timestamp('diajukan_at')->nullable()->after('diajukan_oleh');
            $table->uuid('disetujui_oleh')->nullable()->after('diajukan_at');
            $table->timestamp('disetujui_at')->nullable()->after('disetujui_oleh');
            $table->text('catatan_approval')->nullable()->after('disetujui_at');
            $table->timestamp('diposting_at')->nullable()->after('catatan_approval');
            $table->uuid('dibatalkan_oleh')->nullable()->after('diposting_at');
            $table->timestamp('dibatalkan_at')->nullable()->after('dibatalkan_oleh');
            $table->text('alasan_pembatalan')->nullable()->after('dibatalkan_at');
            $table->char('adjustment_pembalik_id', 36)->nullable()->after('alasan_pembatalan');
            $table->string('tindak_lanjut_kelebihan_bayar', 20)->default('TIDAK_ADA')->after('adjustment_pembalik_id');
            $table->softDeletes()->after('updated_at');

            $table->unique('nomor_adjustment');
            $table->index(['tagihan_id', 'status']);
            $table->foreign('diajukan_oleh')->references('id')->on('users')->nullOnDelete();
            $table->foreign('disetujui_oleh')->references('id')->on('users')->nullOnDelete();
            $table->foreign('dibatalkan_oleh')->references('id')->on('users')->nullOnDelete();
            $table->foreign('adjustment_pembalik_id')->references('id')->on('keuangan_adjustments')->nullOnDelete();
        });

        // Strategi Backfill: Update data lama menjadi DIPOSTING (Legacy records)
        DB::table('keuangan_adjustments')->whereNull('nomor_adjustment')->orderBy('created_at')->chunk(100, function ($adjustments) {
            foreach ($adjustments as $adj) {
                $uniqueId = substr($adj->id, 0, 8); // Ambil 8 karakter pertama UUID untuk nomor unik
                DB::table('keuangan_adjustments')->where('id', $adj->id)->update([
                    'status' => 'DIPOSTING',
                    'nomor_adjustment' => 'ADJ/OLD/' . date('Y/m', strtotime($adj->created_at ?? now())) . '/' . strtoupper($uniqueId),
                    'diposting_at' => $adj->created_at ?? now(),
                    'tindak_lanjut_kelebihan_bayar' => 'TIDAK_ADA',
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('keuangan_adjustments', function (Blueprint $table): void {
            $table->dropForeign(['diajukan_oleh']);
            $table->dropForeign(['disetujui_oleh']);
            $table->dropForeign(['dibatalkan_oleh']);
            $table->dropForeign(['adjustment_pembalik_id']);
            $table->dropUnique(['nomor_adjustment']);
            $table->dropIndex(['tagihan_id', 'status']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'nomor_adjustment', 'status', 'diajukan_oleh', 'diajukan_at',
                'disetujui_oleh', 'disetujui_at', 'catatan_approval', 'diposting_at',
                'dibatalkan_oleh', 'dibatalkan_at', 'alasan_pembatalan',
                'adjustment_pembalik_id', 'tindak_lanjut_kelebihan_bayar',
            ]);
        });
    }
};