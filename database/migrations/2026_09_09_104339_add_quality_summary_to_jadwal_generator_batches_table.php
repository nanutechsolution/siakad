<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom ringkasan kualitas per batch (level agregat).
     * Level per-assignment sudah tertampung di
     * jadwal_generator_results.optimization_score (sudah ada, tidak perlu migration).
     *
     * quality_summary disimpan sebagai JSON supaya fleksibel menambah metrik baru
     * (mis. std deviasi beban per hari, rata-rata skor, dst) tanpa migration lagi.
     */
    public function up(): void
    {
        Schema::table('jadwal_generator_batches', function (Blueprint $table) {
            $table->decimal('quality_score', 5, 2)->nullable()->after('total_failed');
            $table->json('quality_summary')->nullable()->after('quality_score');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_generator_batches', function (Blueprint $table) {
            $table->dropColumn(['quality_score', 'quality_summary']);
        });
    }
};
