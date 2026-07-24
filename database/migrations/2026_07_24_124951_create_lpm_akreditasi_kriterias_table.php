<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_akreditasi_kriterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('akreditasi_id')->constrained('lpm_akreditasis')->cascadeOnDelete();
            $table->string('kode_kriteria', 20);
            $table->string('nama_kriteria', 255);
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();

            $table->index(['akreditasi_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_akreditasi_kriterias');
    }
};
