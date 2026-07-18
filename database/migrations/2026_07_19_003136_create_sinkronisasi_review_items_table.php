<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinkronisasi_review_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sinkronisasi_batch_id')
                ->constrained('sinkronisasi_batches')
                ->cascadeOnDelete();

            $table->uuid('tagihan_id');
            $table->foreign('tagihan_id')->references('id')->on('tagihan_mahasiswas')->cascadeOnDelete();

            $table->foreignId('tagihan_detail_id')
                ->constrained('tagihan_mahasiswas_details')
                ->cascadeOnDelete();

            $table->uuid('mahasiswa_id');
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->cascadeOnDelete();

            $table->foreignId('komponen_biaya_id')
                ->constrained('keuangan_komponen_biaya');

            $table->decimal('nominal_existing', 19, 2);
            $table->decimal('nominal_skema_baru', 19, 2);

            // Status generik, sengaja TIDAK diikat ke istilah "adjustment"
            // supaya modul ini tidak berasumsi hasil review selalu berakhir
            // sebagai Adjustment. PENDING: baru terdeteksi, belum disentuh.
            // IN_PROGRESS: sedang diklaim oleh seorang admin untuk ditindak-
            // lanjuti (state transisi singkat untuk mencegah race condition
            // dua admin memproses baris yang sama). RESOLVED: sudah
            // ditindaklanjuti (mis. adjustment sudah dibuat). IGNORED: admin
            // sengaja mengabaikan temuan ini.
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'RESOLVED', 'IGNORED'])
                ->default('PENDING');

            // Ditautkan HANYA jika tindak lanjutnya berupa pembuatan
            // Adjustment lewat modul Adjustment yang sudah ada. Nullable
            // karena tidak semua resolusi berakhir sebagai adjustment
            // (bisa saja diabaikan / ditindaklanjuti lewat cara lain).
            $table->uuid('keuangan_adjustment_id')->nullable();
            $table->foreign('keuangan_adjustment_id')
                ->references('id')->on('keuangan_adjustments')
                ->nullOnDelete();

            $table->uuid('resolved_by')->nullable();
            $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->text('catatan_resolusi')->nullable();

            $table->timestamps();

            // Satu baris detail tagihan hanya boleh punya SATU temuan yang
            // masih terbuka (PENDING/IN_PROGRESS) pada satu waktu. Constraint
            // ini tidak bisa dibuat "partial unique" di MySQL, jadi
            // idempotency untuk kasus re-run sinkronisasi ditegakkan di
            // level aplikasi (lock + cek status sebelum insert baris baru -
            // lihat SinkronisasiTagihanJob), bukan murni di level DB.
            $table->index(['tagihan_detail_id', 'status']);
            $table->index(['sinkronisasi_batch_id', 'status']);
            $table->index(['mahasiswa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinkronisasi_review_items');
    }
};