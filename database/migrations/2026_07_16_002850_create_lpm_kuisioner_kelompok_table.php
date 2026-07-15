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
        Schema::create('lpm_kuisioner_kelompok', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tahun_akademik_id')->nullable()->index('lpm_kuisioner_kelompok_tahun_akademik_id_foreign');
            $table->string('nama_kelompok');
            $table->string('kategori', 50)->default('EDOM');
            $table->integer('urutan')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpm_kuisioner_kelompok');
    }
};
