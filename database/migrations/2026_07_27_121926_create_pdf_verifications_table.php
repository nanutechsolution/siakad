<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_verifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('pdf_document_id')->nullable();
            $table->string('nomor_dokumen_diminta', 100);
            $table->boolean('ditemukan')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('pdf_document_id')->references('id')->on('pdf_documents')->nullOnDelete();
            $table->index('nomor_dokumen_diminta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_verifications');
    }
};
