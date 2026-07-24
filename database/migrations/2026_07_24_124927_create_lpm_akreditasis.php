<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_akreditasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lpm_akreditasi_lembagas')->cascadeOnDelete();
            // Nullable: kosong berarti akreditasi tingkat institusi (bukan per prodi).
            $table->foreignId('prodi_id')->nullable()->constrained('ref_prodi')->cascadeOnDelete();
            $table->enum('jenis_akreditasi', ['INSTITUSI', 'PRODI']);
            $table->string('instrumen', 100)->nullable()->comment('mis. IAPS 4.0, IAPT 3.0');
            $table->enum('status', ['PERSIAPAN', 'PENGISIAN', 'SUBMIT', 'VISITASI', 'SELESAI'])->default('PERSIAPAN');
            $table->string('peringkat_target', 50)->nullable();
            $table->string('peringkat_hasil', 50)->nullable();
            $table->date('tanggal_submit')->nullable();
            $table->date('tanggal_visitasi')->nullable();
            $table->date('berlaku_sampai')->nullable();
            $table->timestamps();

            $table->index(['jenis_akreditasi', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_akreditasis');
    }
};
