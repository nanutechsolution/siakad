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
        Schema::create('lpm_edom_progress', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('mahasiswa_id', 36);
            $table->char('jadwal_kuliah_id', 36)->index('lpm_edom_progress_jadwal_kuliah_id_foreign');
            $table->char('dosen_id', 36)->nullable()->index('lpm_edom_progress_dosen_id_foreign');
            $table->boolean('is_completed')->default(true);
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'jadwal_kuliah_id', 'dosen_id'], 'uq_mhs_jadwal_dosen_edom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_edom_progress');
    }
};
