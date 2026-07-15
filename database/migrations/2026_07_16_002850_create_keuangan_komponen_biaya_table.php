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
        Schema::create('keuangan_komponen_biaya', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kode_komponen', 30)->unique('uk_kode_komponen');
            $table->string('nama_komponen', 100);
            $table->enum('tipe_biaya', ['TETAP', 'SKS', 'SEKALI', 'INSIDENTAL']);
            $table->integer('urutan_prioritas')->default(99);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_komponen_biaya');
    }
};
