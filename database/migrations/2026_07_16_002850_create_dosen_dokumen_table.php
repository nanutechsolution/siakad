<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dosen_dokumen', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('dosen_id', 36);
            $table->unsignedBigInteger('ref_dokumen_dosen_id')->index('dosen_dokumen_ref_dokumen_dosen_id_foreign');
            $table->string('file_path');
            $table->string('nama_file_asli')->nullable();
            $table->unsignedInteger('ukuran_kb')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->char('reviewed_by', 36)->nullable()->index('dosen_dokumen_reviewed_by_foreign');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();

            $table->unique(['dosen_id', 'ref_dokumen_dosen_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_dokumen');
    }
};
