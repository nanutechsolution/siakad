<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_generator_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tahun_akademik_id');
            $table->unsignedBigInteger('prodi_id');
            $table->json('config_snapshot')->comment('Simpan konfigurasi hari, jam operasional, dan slot SKS');
            $table->enum('status', ['RUNNING', 'PREVIEW', 'COMMITTED', 'FAILED'])->default('RUNNING');
            $table->integer('total_generated')->default(0);
            $table->integer('total_failed')->default(0);
            $table->char('created_by', 36)->nullable();
            $table->timestamps();

            $table->foreign('tahun_akademik_id')->references('id')->on('ref_tahun_akademik')->onDelete('cascade');
            $table->foreign('prodi_id')->references('id')->on('ref_prodi')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_generator_batches');
    }
};
