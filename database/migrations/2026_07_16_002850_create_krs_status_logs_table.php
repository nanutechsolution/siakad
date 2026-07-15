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
        Schema::create('krs_status_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('krs_id', 36)->index('krs_status_logs_krs_id_foreign');
            $table->enum('aksi', ['DIAJUKAN', 'DISETUJUI', 'DITOLAK', 'DIBATALKAN', 'DIBUKA_KEMBALI', 'DIUBAH_ADMIN']);
            $table->char('dilakukan_oleh', 36)->nullable()->index('krs_status_logs_dilakukan_oleh_foreign');
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
        Schema::dropIfExists('krs_status_logs');
    }
};
