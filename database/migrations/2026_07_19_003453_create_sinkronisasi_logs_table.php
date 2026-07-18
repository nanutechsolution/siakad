<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel append-only (tidak ada updated_at) - satu baris dicatat
        // sekali per mahasiswa per batch, tidak pernah diupdate setelahnya.
        // Ini yang menjawab kebutuhan "ditelusuri tanpa harus membaca log
        // aplikasi": admin bisa lihat persis mahasiswa mana yang berhasil/
        // gagal/dilewati pada batch tertentu langsung dari database/UI.
        Schema::create('sinkronisasi_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sinkronisasi_batch_id')
                ->constrained('sinkronisasi_batches')
                ->cascadeOnDelete();

            $table->uuid('mahasiswa_id');
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->cascadeOnDelete();

            $table->enum('status', ['BERHASIL', 'GAGAL', 'DILEWATI'])->index();

            $table->unsignedSmallInteger('jumlah_ditambah')->default(0);
            $table->unsignedSmallInteger('jumlah_review')->default(0);
            $table->unsignedSmallInteger('jumlah_warning')->default(0);

            // Pesan bebas: alasan dilewati, pesan exception kalau gagal,
            // atau ringkasan komponen apa saja yang ditambahkan kalau
            // berhasil.
            $table->text('pesan')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['sinkronisasi_batch_id', 'status']);
            $table->index(['mahasiswa_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinkronisasi_logs');
    }
};