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
        Schema::create('lpm_edom_saran', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('jadwal_kuliah_id', 36);
            $table->char('dosen_id', 36)->nullable()->index('lpm_edom_saran_dosen_id_foreign');
            $table->text('catatan');
            $table->timestamps();

            $table->index(['jadwal_kuliah_id', 'dosen_id'], 'idx_edom_saran_jadwal_dosen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_edom_saran');
    }
};
