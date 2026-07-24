<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_akreditasi_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elemen_id')->constrained('lpm_akreditasi_elemens')->cascadeOnDelete();
            $table->foreignId('indikator_id')->nullable()->constrained('lpm_akreditasi_indikators')->nullOnDelete();
            $table->string('file_path', 255);
            $table->string('keterangan', 255)->nullable();
            $table->foreignId('uploaded_by_person_id')->nullable()->constrained('ref_person')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_akreditasi_evidences');
    }
};
