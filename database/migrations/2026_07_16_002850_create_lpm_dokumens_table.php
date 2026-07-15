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
        Schema::create('lpm_dokumens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_dokumen', 50)->nullable()->unique();
            $table->string('nama_dokumen');
            $table->enum('jenis', ['KEBIJAKAN', 'MANUAL', 'STANDAR', 'FORMULIR']);
            $table->unsignedBigInteger('prodi_id')->nullable();
            $table->string('file_path');
            $table->text('deskripsi')->nullable();
            $table->string('versi')->default('1.0');
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED'])->default('PUBLISHED');
            $table->boolean('is_active')->default(true);
            $table->date('tgl_berlaku');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_dokumens');
    }
};
