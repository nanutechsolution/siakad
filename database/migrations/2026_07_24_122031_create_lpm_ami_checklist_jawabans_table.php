<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_ami_checklist_jawabans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('lpm_ami_programs')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('lpm_ami_checklist_items')->cascadeOnDelete();
            $table->enum('jawaban', ['SESUAI', 'TIDAK_SESUAI', 'OBSERVASI'])->nullable();
            $table->text('catatan')->nullable();
            // Terisi otomatis kalau jawaban memicu temuan resmi (TIDAK_SESUAI/OBSERVASI
            // yang ditindaklanjuti menjadi baris di lpm_ami_findings).
            $table->foreignId('finding_id')->nullable()->constrained('lpm_ami_findings')->nullOnDelete();
            $table->timestamps();

            $table->unique(['program_id', 'checklist_item_id'], 'unique_jawaban_per_program_item');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_ami_checklist_jawabans');
    }
};
