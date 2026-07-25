<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('document_type', 50);
            $table->string('classification', 20);
            $table->string('documentable_type', 150)->nullable();
            $table->string('documentable_id', 36)->nullable();
            $table->string('nomor_dokumen', 100)->nullable();
            $table->string('file_disk', 50)->default('local');
            $table->string('file_path', 255);
            $table->char('file_hash', 64);
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_current')->default(true);
            $table->string('status', 20)->default('draft');
            $table->json('metadata')->nullable();
            $table->char('generated_by', 36)->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique('nomor_dokumen');
            $table->index(
                ['document_type', 'documentable_type', 'documentable_id', 'is_current'],
                'idx_pdfdoc_lookup'
            );
            $table->foreign('generated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_documents');
    }
};
