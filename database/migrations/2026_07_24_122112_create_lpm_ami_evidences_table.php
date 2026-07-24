<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_ami_evidences', function (Blueprint $table) {
            $table->id();
            // Nullable & saling eksklusif secara praktik: evidence bisa melekat ke
            // jawaban checklist (bukti kepatuhan/ketidaksesuaian) ATAU langsung ke
            // temuan (bukti tambahan saat tindak lanjut).
            $table->foreignId('checklist_jawaban_id')->nullable()->constrained('lpm_ami_checklist_jawabans')->cascadeOnDelete();
            $table->foreignId('finding_id')->nullable()->constrained('lpm_ami_findings')->cascadeOnDelete();
            $table->string('file_path', 255);
            $table->string('keterangan', 255)->nullable();
            $table->foreignId('uploaded_by_person_id')->nullable()->constrained('ref_person')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_ami_evidences');
    }
};
