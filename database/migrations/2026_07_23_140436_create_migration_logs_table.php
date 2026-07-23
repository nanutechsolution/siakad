<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('migration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('migration_batch_id')
                ->constrained('migration_batches')
                ->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('nim', 20)->nullable();
            $table->uuid('mahasiswa_id')->nullable();
            $table->unsignedBigInteger('krs_detail_id')->nullable();
            $table->enum('status', ['BERHASIL', 'GAGAL', 'DILEWATI']);
            $table->text('pesan')->nullable();
            $table->json('row_data');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswas')->nullOnDelete();
            $table->foreign('krs_detail_id')->references('id')->on('krs_detail')->nullOnDelete();
            $table->index(['migration_batch_id', 'status']);
            $table->index(['mahasiswa_id', 'created_at']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('migration_logs');
    }
};
