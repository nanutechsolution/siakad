<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOTE: kolom `kategori` (enum AKADEMIK/NON-AKADEMIK) di lpm_standars TIDAK
 * dihapus — tetap dipertahankan untuk kompatibilitas data lama. Kolom baru
 * `kategori_standar_id` menambahkan granularitas (Pendidikan/Penelitian/
 * Pengabdian/Tambahan Universitas) tanpa memutus data existing. Nullable
 * dulu agar aman untuk baris lama, bisa di-backfill lalu diperketat NOT NULL
 * di migration terpisah setelah data lama dipetakan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lpm_standars', function (Blueprint $table) {
            $table->foreignId('kategori_standar_id')
                ->nullable()
                ->after('kategori')
                ->constrained('lpm_kategori_standars')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lpm_standars', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kategori_standar_id');
        });
    }
};
