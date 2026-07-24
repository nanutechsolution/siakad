<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_akreditasi_elemens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kriteria_id')->constrained('lpm_akreditasi_kriterias')->cascadeOnDelete();
            $table->string('kode_elemen', 20);
            $table->text('deskripsi');
            $table->unsignedInteger('urutan')->default(1);
            $table->enum('status_kelengkapan', ['BELUM', 'PROSES', 'LENGKAP'])->default('BELUM');
            $table->timestamps();

            $table->index(['kriteria_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_akreditasi_elemens');
    }
};