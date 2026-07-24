<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_ami_program_auditors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('lpm_ami_programs')->cascadeOnDelete();
            $table->foreignId('auditor_id')->constrained('lpm_auditors')->cascadeOnDelete();
            $table->enum('peran', ['KETUA_TIM', 'ANGGOTA'])->default('ANGGOTA');
            $table->timestamps();

            $table->unique(['program_id', 'auditor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_ami_program_auditors');
    }
};