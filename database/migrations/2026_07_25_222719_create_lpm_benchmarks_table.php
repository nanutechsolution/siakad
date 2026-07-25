<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_benchmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('lpm_indikators')->cascadeOnDelete();
            $table->foreignId('institusi_pembanding_id')->constrained('lpm_benchmark_institusis')->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->decimal('nilai_internal', 10, 2)->nullable();
            $table->decimal('nilai_eksternal', 10, 2)->nullable();
            $table->text('analisis_gap')->nullable();
            $table->string('sumber_data', 255)->nullable();
            $table->timestamps();

            $table->unique(['indikator_id', 'institusi_pembanding_id', 'tahun'], 'unique_benchmark_per_tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_benchmarks');
    }
};
