<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_survey_analisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_id')->constrained('lpm_kuisioner_kelompok')->cascadeOnDelete();
            $table->foreignId('tahun_akademik_id')->nullable()->constrained('ref_tahun_akademik')->nullOnDelete();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('lpm_unit_kerjas')->nullOnDelete();
            $table->decimal('rata_rata_skor', 5, 2)->nullable();
            $table->text('kesimpulan')->nullable();
            $table->text('rencana_perbaikan')->nullable();
            $table->foreignId('disusun_oleh_person_id')->nullable()->constrained('ref_person')->nullOnDelete();
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_survey_analisis');
    }
};
