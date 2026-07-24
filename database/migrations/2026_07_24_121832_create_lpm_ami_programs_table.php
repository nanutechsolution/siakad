<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_ami_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('lpm_ami_periodes')->cascadeOnDelete();
            $table->foreignId('unit_kerja_id')->constrained('lpm_unit_kerjas')->cascadeOnDelete();
            $table->date('tanggal_pelaksanaan')->nullable();
            $table->enum('status', ['DIJADWALKAN', 'BERLANGSUNG', 'SELESAI'])->default('DIJADWALKAN');
            $table->timestamps();

            $table->index(['periode_id', 'unit_kerja_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_ami_programs');
    }
};
