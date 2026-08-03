<?php

use App\Enums\PembimbingAkademikMode; // kalau pakai Enum PHP
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konfigurasi_pembimbing_akademik', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prodi_id')
                ->constrained('ref_prodi')
                ->restrictOnDelete();

            $table->integer('angkatan_id');

            $table->foreign('angkatan_id')
                ->references('id_tahun')
                ->on('ref_angkatan')
                ->restrictOnDelete();

            $table->enum('mode', [
                'PER_KELAS',
                'PER_MAHASISWA',
            ])->default('PER_KELAS');

            $table->boolean('aktif')
                ->default(true);

            $table->text('keterangan')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'prodi_id',
                'angkatan_id',
            ], 'uniq_konfigurasi_pembimbing');

            $table->index('mode');
            $table->index('aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konfigurasi_pembimbing_akademik');
    }
};
