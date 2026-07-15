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
        Schema::create('master_mata_kuliahs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('prodi_id');
            $table->string('kode_mk', 20);
            $table->string('nama_mk', 200);
            $table->integer('sks_default')->default(3);
            $table->integer('sks_tatap_muka')->default(0);
            $table->integer('sks_praktek')->default(0);
            $table->integer('sks_lapangan')->default(0);
            $table->char('jenis_mk', 1)->default('A');
            $table->string('activity_type', 20)->default('REGULAR');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['prodi_id', 'kode_mk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_mata_kuliahs');
    }
};
