<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_ketersediaans', function (Blueprint $table) {
            $table->id();
            // Sesuaikan tipe data dosen_id dengan database Anda (jika UUID gunakan uuid(), jika integer gunakan foreignId())
            $table->uuid('dosen_id');

            $table->string('hari'); // Senin, Selasa, dst
            $table->time('jam_mulai');
            $table->time('jam_selesai');

            $table->timestamps();

            // Opsional: Relasi ke tabel dosen
            // $table->foreign('dosen_id')->references('id')->on('trx_dosens')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen_ketersediaans');
    }
};
