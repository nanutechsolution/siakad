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
        Schema::table('jadwal_komponen_nilai', function (Blueprint $table) {
            $table->foreign(['jadwal_kuliah_id'])->references(['id'])->on('jadwal_kuliah')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['komponen_id'])->references(['id'])->on('ref_komponen_nilai')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_komponen_nilai', function (Blueprint $table) {
            $table->dropForeign('jadwal_komponen_nilai_jadwal_kuliah_id_foreign');
            $table->dropForeign('jadwal_komponen_nilai_komponen_id_foreign');
        });
    }
};
