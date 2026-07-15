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
        Schema::create('ref_gelar', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode', 10)->unique();
            $table->string('nama');
            $table->enum('posisi', ['DEPAN', 'BELAKANG'])->default('BELAKANG');
            $table->enum('jenjang', ['D3', 'D4', 'S1', 'S2', 'S3', 'PROFESI']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_gelar');
    }
};
