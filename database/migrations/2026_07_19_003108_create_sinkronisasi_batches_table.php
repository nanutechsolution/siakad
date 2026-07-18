<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinkronisasi_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tahun_akademik_id')
                ->constrained('ref_tahun_akademik');

            // Mode: DRY_RUN tidak pernah menulis ke tagihan_mahasiswas_details
            // atau sinkronisasi_review_items, hanya menghasilkan summary_snapshot.
            $table->enum('mode', ['DRY_RUN', 'EKSEKUSI'])->default('EKSEKUSI');

            // Status siklus hidup batch.
            $table->enum('status', ['PROCESSING', 'COMPLETED', 'FAILED'])
                ->default('PROCESSING');

            // === Audit reproducibility ===
            // Simpan persis parameter form (tipe_target, tahun_akademik_id,
            // prodi_id, angkatan_id, mahasiswa_id) apa adanya saat batch
            // dijalankan. Ini memungkinkan batch lama direproduksi/diaudit
            // meskipun data master (prodi, angkatan) berubah di kemudian hari.
            $table->json('parameter_snapshot');

            // Ringkasan hasil akhir: breakdown lengkap per kategori (jumlah
            // mahasiswa, daftar komponen yang di-warn beserta jumlah
            // mahasiswa terdampak, dll). Diisi saat batch selesai (COMPLETED
            // atau FAILED). Ini yang menjadi sumber halaman Riwayat tanpa
            // perlu query ulang ke tabel transaksional.
            $table->json('summary_snapshot')->nullable();

            // Counter agregat cepat (redundant dengan summary_snapshot, tapi
            // dibuat kolom terpisah supaya bisa di-index dan ditampilkan di
            // tabel listing tanpa parsing JSON).
            $table->unsignedInteger('total_mahasiswa')->default(0);
            $table->unsignedInteger('total_ditambah')->default(0);
            $table->unsignedInteger('total_review')->default(0);
            $table->unsignedInteger('total_warning')->default(0);
            $table->unsignedInteger('total_dilewati')->default(0);
            $table->unsignedInteger('total_error')->default(0);

            $table->text('error_message')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->uuid('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['tahun_akademik_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinkronisasi_batches');
    }
};
