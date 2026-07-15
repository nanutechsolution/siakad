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
        Schema::create('kelas_dosen_wali', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kelas_id')->index('kelas_dosen_wali_kelas_id_foreign');
            $table->char('dosen_id', 36)->index('kelas_dosen_wali_dosen_id_foreign');
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_dosen_wali');
    }
};
