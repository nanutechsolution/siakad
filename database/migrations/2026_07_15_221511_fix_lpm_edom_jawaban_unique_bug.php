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
        Schema::table('lpm_edom_jawaban', function (Blueprint $table) {
            $table->dropUnique('unique_edom_jawaban_dosen');

            // Index gabungan untuk agregasi IKD per dosen/kelas/pertanyaan (lihat sql/edom_reporting_queries.sql)
            $table->index(['dosen_id', 'jadwal_kuliah_id', 'pertanyaan_id'], 'idx_edom_jawaban_agregasi');
        });
    }

    public function down(): void
    {
        Schema::table('lpm_edom_jawaban', function (Blueprint $table) {
            $table->dropIndex('idx_edom_jawaban_agregasi');
            $table->unique(['pertanyaan_id', 'dosen_id'], 'unique_edom_jawaban_dosen');
        });
    }
};
