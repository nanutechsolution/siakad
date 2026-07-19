<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generator_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tahun_akademik_id')
                ->constrained('ref_tahun_akademik');

            $table->enum('status', ['PROCESSING', 'COMPLETED', 'FAILED'])
                ->default('PROCESSING');

            // Snapshot persis parameter form (tipe_target, prodi_id,
            // angkatan_id, mahasiswa_id) - sama seperti sinkronisasi_batches,
            // supaya batch generate lama tetap bisa direproduksi/diaudit
            // walau data master prodi/angkatan berubah kemudian.
            $table->json('parameter_snapshot');
            $table->json('summary_snapshot')->nullable();

            $table->unsignedInteger('total_mahasiswa')->default(0);
            $table->unsignedInteger('total_berhasil')->default(0);
            $table->unsignedInteger('total_gagal')->default(0);
            $table->unsignedInteger('total_skip')->default(0);

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
        Schema::dropIfExists('generator_batches');
    }
};
