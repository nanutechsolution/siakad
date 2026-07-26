<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_signature_authorities', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 50);
            $table->foreignId('jabatan_id')->constrained('ref_jabatan');
            $table->unsignedTinyInteger('urutan')->default(1);
            $table->string('label', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['document_type', 'is_active', 'urutan'], 'idx_signature_authority_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_signature_authorities');
    }
};
