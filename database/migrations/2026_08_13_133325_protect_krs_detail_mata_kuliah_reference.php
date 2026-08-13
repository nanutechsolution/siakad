<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION 6 — krs_detail.mata_kuliah_id sebelumnya SET NULL, sehingga baris
 * KRS/nilai historis kehilangan konteks mata kuliah apa yang diambil ketika
 * master_mata_kuliahs dihapus. master_mata_kuliahs sudah punya deleted_at,
 * jadi hard delete seharusnya memang tidak diizinkan sama sekali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('krs_detail', function (Blueprint $table) {
            $table->dropForeign(['mata_kuliah_id']);
            $table->foreign('mata_kuliah_id')->references('id')->on('master_mata_kuliahs')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('krs_detail', function (Blueprint $table) {
            $table->dropForeign(['mata_kuliah_id']);
            $table->foreign('mata_kuliah_id')->references('id')->on('master_mata_kuliahs')->onDelete('set null');
        });
    }
};
