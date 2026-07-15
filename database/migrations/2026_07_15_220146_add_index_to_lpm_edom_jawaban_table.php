<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lpm_edom_jawaban', function (Blueprint $table) {
            // Indexing untuk agregasi nilai kuesioner yang lebih cepat
            $table->index('pertanyaan_id', 'idx_edom_pertanyaan');
        });
    }

    public function down(): void
    {
        Schema::table('lpm_edom_jawaban', function (Blueprint $table) {
            $table->dropIndex('idx_edom_pertanyaan');
        });
    }
};