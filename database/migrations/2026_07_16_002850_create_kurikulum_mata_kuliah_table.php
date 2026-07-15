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
        Schema::create('kurikulum_mata_kuliah', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kurikulum_id');
            $table->unsignedBigInteger('mata_kuliah_id')->index('kurikulum_mata_kuliah_mata_kuliah_id_foreign');
            $table->integer('semester_paket');
            $table->integer('sks_tatap_muka');
            $table->integer('sks_praktek')->default(0);
            $table->integer('sks_lapangan')->default(0);
            $table->char('sifat_mk', 1)->default('W');
            $table->timestamps();

            $table->unique(['kurikulum_id', 'mata_kuliah_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurikulum_mata_kuliah');
    }
};
