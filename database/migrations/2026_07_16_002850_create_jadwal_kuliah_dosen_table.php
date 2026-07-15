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
        Schema::create('jadwal_kuliah_dosen', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('jadwal_kuliah_id', 36)->index('jadwal_kuliah_dosen_jadwal_kuliah_id_foreign');
            $table->char('dosen_id', 36)->index('jadwal_kuliah_dosen_dosen_id_foreign');
            $table->boolean('is_koordinator')->default(false);
            $table->boolean('is_penilai')->default(false);
            $table->integer('rencana_tatap_muka')->default(14);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kuliah_dosen');
    }
};
