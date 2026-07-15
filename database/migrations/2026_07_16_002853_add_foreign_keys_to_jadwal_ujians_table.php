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
        Schema::table('jadwal_ujians', function (Blueprint $table) {
            $table->foreign(['jadwal_kuliah_id'])->references(['id'])->on('jadwal_kuliah')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ruang_id'])->references(['id'])->on('ref_ruang')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_ujians', function (Blueprint $table) {
            $table->dropForeign('jadwal_ujians_jadwal_kuliah_id_foreign');
            $table->dropForeign('jadwal_ujians_ruang_id_foreign');
        });
    }
};
