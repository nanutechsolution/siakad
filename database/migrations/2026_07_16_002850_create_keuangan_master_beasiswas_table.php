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
        Schema::create('keuangan_master_beasiswas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama_beasiswa', 150);
            $table->enum('kategori', ['INTERNAL', 'EKSTERNAL', 'PEMERINTAH']);
            $table->text('keterangan')->nullable();
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
        Schema::dropIfExists('keuangan_master_beasiswas');
    }
};
