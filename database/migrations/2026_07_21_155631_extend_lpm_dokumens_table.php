<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * NOTE: kolom `prodi_id` di lpm_dokumens TETAP dipertahankan. Kolom baru
 * `unit_kerja_id` menambahkan opsi scope ke unit non-akademik (Lembaga/Biro/
 * Universitas), dan `standar_id` menghubungkan dokumen ke standar terkait
 * (opsional). Enum `jenis` ditambah SOP & DOKUMEN_PENDUKUNG, enum `status`
 * ditambah REVIEW — dilakukan lewat ALTER MODIFY mentah karena Laravel/
 * Doctrine tidak punya cara aman mengubah enum lewat Blueprint biasa.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `lpm_dokumens`
            MODIFY COLUMN `jenis` ENUM('KEBIJAKAN','MANUAL','STANDAR','FORMULIR','SOP','DOKUMEN_PENDUKUNG')
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
        ");

        DB::statement("
            ALTER TABLE `lpm_dokumens`
            MODIFY COLUMN `status` ENUM('DRAFT','REVIEW','PUBLISHED','ARCHIVED')
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT'
        ");

        Schema::table('lpm_dokumens', function (Blueprint $table) {
            $table->foreignId('unit_kerja_id')
                ->nullable()
                ->after('prodi_id')
                ->constrained('lpm_unit_kerjas')
                ->nullOnDelete();
            $table->foreignId('standar_id')
                ->nullable()
                ->after('unit_kerja_id')
                ->constrained('lpm_standars')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lpm_dokumens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('standar_id');
            $table->dropConstrainedForeignId('unit_kerja_id');
        });

        DB::statement("
            ALTER TABLE `lpm_dokumens`
            MODIFY COLUMN `status` ENUM('DRAFT','PUBLISHED','ARCHIVED')
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PUBLISHED'
        ");

        DB::statement("
            ALTER TABLE `lpm_dokumens`
            MODIFY COLUMN `jenis` ENUM('KEBIJAKAN','MANUAL','STANDAR','FORMULIR')
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
        ");
    }
};
