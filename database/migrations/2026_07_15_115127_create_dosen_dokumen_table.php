<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_dokumen', function (Blueprint $table) {
            $table->id();
            $table->char('dosen_id', 36);
            $table->foreignId('ref_dokumen_dosen_id')
                ->constrained('ref_dokumen_dosen')
                ->cascadeOnDelete();

            $table->string('file_path');
            $table->string('nama_file_asli')->nullable();
            $table->unsignedInteger('ukuran_kb')->nullable();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->char('reviewed_by', 36)->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_note')->nullable();

            $table->timestamps();

            // satu dosen hanya punya 1 dokumen aktif per jenis dokumen
            $table->unique(['dosen_id', 'ref_dokumen_dosen_id']);
            $table->foreign('dosen_id')
                ->references('id')->on('trx_dosen')
                ->cascadeOnDelete();
            $table->foreign('reviewed_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen_dokumen');
    }
};