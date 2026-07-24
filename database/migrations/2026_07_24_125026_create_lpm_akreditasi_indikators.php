<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_akreditasi_indikators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elemen_id')->constrained('lpm_akreditasi_elemens')->cascadeOnDelete();
            $table->text('deskripsi');
            $table->decimal('bobot', 5, 2)->nullable();
            // Opsional: kalau butir borang ini bisa dipenuhi otomatis dari indikator
            // SIAKAD yang sudah ada (lpm_indikators), tautkan supaya tidak re-entry data.
            $table->foreignId('indikator_siakad_id')->nullable()->constrained('lpm_indikators')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_akreditasi_indikators');
    }
};