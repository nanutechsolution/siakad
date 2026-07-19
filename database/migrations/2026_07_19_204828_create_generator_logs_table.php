<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only, sama seperti sinkronisasi_logs - satu baris per
        // mahasiswa per batch, tidak pernah diupdate setelahnya.
        Schema::create('generator_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('generator_batch_id')
                ->constrained('generator_batches')
                ->cascadeOnDelete();

            $table->uuid('mahasiswa_id');
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->cascadeOnDelete();

            $table->enum('status', ['BERHASIL', 'GAGAL', 'DILEWATI'])->index();

            // Diisi hanya kalau status BERHASIL - nominal tagihan yang
            // benar-benar terbit untuk mahasiswa ini di batch ini.
            $table->decimal('total_tagihan', 19, 2)->nullable();

            $table->text('pesan')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['generator_batch_id', 'status']);
            $table->index(['mahasiswa_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generator_logs');
    }
};
