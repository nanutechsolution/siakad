<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_dokumen_riwayats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('lpm_dokumens')->cascadeOnDelete();
            $table->string('versi_lama', 50);
            $table->string('versi_baru', 50);
            $table->string('file_path', 255);
            $table->text('changelog')->nullable();
            $table->foreignId('diubah_oleh_person_id')->nullable()->constrained('ref_person')->nullOnDelete();
            $table->date('tanggal');
            $table->timestamps();

            $table->index(['dokumen_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_dokumen_riwayats');
    }
};
