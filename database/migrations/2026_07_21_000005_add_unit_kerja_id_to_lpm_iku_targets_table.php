<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOTE: kolom `prodi_id` di lpm_iku_targets TETAP dipertahankan (dipakai untuk
 * target level program studi seperti sekarang). Kolom baru `unit_kerja_id`
 * ditambahkan agar target IKU juga bisa discope ke unit non-akademik
 * (Lembaga/Biro/Universitas) lewat lpm_unit_kerjas, tanpa mengubah constraint
 * unique existing (indikator_id, prodi_id, tahun).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lpm_iku_targets', function (Blueprint $table) {
            $table->foreignId('unit_kerja_id')
                ->nullable()
                ->after('prodi_id')
                ->constrained('lpm_unit_kerjas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lpm_iku_targets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_kerja_id');
        });
    }
};
