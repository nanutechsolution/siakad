<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_kampus', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kampus', 50)->unique(); // Misal: KMP-01
            $table->string('nama_kampus'); // Misal: Kampus Utama Tambolaka
            $table->text('alamat')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_kampus');
    }
};
