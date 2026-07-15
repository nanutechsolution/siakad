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
        Schema::create('dispensasi_akademik_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('dispensasi_id', 36)->index('dispensasi_akademik_logs_dispensasi_id_foreign');
            $table->enum('aksi', ['DIBUAT', 'DIUPDATE', 'DISETUJUI', 'DITOLAK', 'DIBATALKAN', 'EXPIRED']);
            $table->char('dilakukan_oleh', 36)->nullable()->index('dispensasi_akademik_logs_dilakukan_oleh_foreign');
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispensasi_akademik_logs');
    }
};
