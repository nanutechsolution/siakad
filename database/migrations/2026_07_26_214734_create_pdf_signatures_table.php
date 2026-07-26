<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_signatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pdf_document_id');
            $table->foreignId('signature_authority_id')->constrained('pdf_signature_authorities');
            $table->unsignedBigInteger('person_id');
            $table->string('nama_penandatangan_snapshot', 150);
            $table->string('jabatan_snapshot', 150);
            $table->unsignedTinyInteger('urutan')->default(1);
            $table->char('document_hash_at_signing', 64);
            $table->timestamp('signed_at');
            $table->string('status', 20)->default('signed');
            $table->timestamps();

            $table->foreign('pdf_document_id')->references('id')->on('pdf_documents')->cascadeOnDelete();
            $table->foreign('person_id')->references('id')->on('ref_person');
            $table->index(['pdf_document_id', 'urutan'], 'idx_pdf_signatures_document');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_signatures');
    }
};
