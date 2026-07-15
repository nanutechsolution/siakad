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
        Schema::create('lpm_standars', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_standar');
            $table->string('nama_standar');
            $table->enum('kategori', ['AKADEMIK', 'NON-AKADEMIK']);
            $table->text('pernyataan_standar');
            $table->integer('target_pencapaian')->default(100);
            $table->string('satuan')->default('%');
            $table->integer('versi')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['kode_standar', 'versi'], 'lpm_standars_kode_versi_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_standars');
    }
};
