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
        Schema::create('kelas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama_kelas');
            $table->unsignedBigInteger('prodi_id')->index('kelas_prodi_id_foreign');
            $table->unsignedBigInteger('program_id')->index('kelas_program_id_foreign');
            $table->integer('angkatan_id');
            $table->integer('kapasitas')->nullable();
            $table->timestamps();

            $table->unique(['nama_kelas', 'prodi_id', 'program_id', 'angkatan_id'], 'uniq_kelas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
