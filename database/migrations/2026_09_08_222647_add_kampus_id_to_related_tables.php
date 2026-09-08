<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah ke tabel Ruang
        Schema::table('ref_ruang', function (Blueprint $table) {
            // nullable() agar data lama tidak error
            $table->foreignId('kampus_id')->nullable()->constrained('ref_kampus')->nullOnDelete();
        });

        // 2. Tambah ke tabel Generator Batches
        Schema::table('jadwal_generator_batches', function (Blueprint $table) {
            $table->foreignId('kampus_id')->nullable()->constrained('ref_kampus')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ref_ruang', function (Blueprint $table) {
            $table->dropForeign(['kampus_id']);
            $table->dropColumn('kampus_id');
        });

        Schema::table('jadwal_generator_batches', function (Blueprint $table) {
            $table->dropForeign(['kampus_id']);
            $table->dropColumn('kampus_id');
        });
    }
};
