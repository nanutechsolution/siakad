<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 50);
            $table->string('kode_unit', 30);
            $table->unsignedSmallInteger('periode_tahun');
            $table->unsignedInteger('last_sequence')->default(0);
            $table->string('format_template', 150);
            $table->timestamps();

            $table->unique(['document_type', 'kode_unit', 'periode_tahun'], 'uniq_pdf_number_sequence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_number_sequences');
    }
};
