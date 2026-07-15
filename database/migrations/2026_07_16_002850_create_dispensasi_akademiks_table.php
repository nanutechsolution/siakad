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
        Schema::create('dispensasi_akademiks', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('mahasiswa_id', 36)->index('dispensasi_akademiks_mahasiswa_id_foreign');
            $table->enum('jenis', ['KRS']);
            $table->text('alasan')->nullable();
            $table->date('berlaku_mulai');
            $table->date('berlaku_sampai');
            $table->enum('status', ['DRAFT', 'AKTIF', 'EXPIRED', 'DIBATALKAN'])->default('DRAFT');
            $table->char('disetujui_oleh', 36)->nullable()->index('dispensasi_akademiks_disetujui_oleh_foreign');
            $table->timestamp('disetujui_pada')->nullable();
            $table->char('created_by', 36)->nullable()->index('dispensasi_akademiks_created_by_foreign');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispensasi_akademiks');
    }
};
