<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_unit_pics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_kerja_id')->constrained('lpm_unit_kerjas')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('ref_person')->cascadeOnDelete();
            $table->enum('peran', ['KETUA', 'SEKRETARIS', 'GKM', 'AUDITOR', 'ANGGOTA']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();

            $table->index(['unit_kerja_id', 'peran']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_unit_pics');
    }
};
