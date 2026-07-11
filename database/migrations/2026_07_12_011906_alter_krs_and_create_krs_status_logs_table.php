<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Alter tabel krs
        Schema::table('krs', function (Blueprint $table) {
            $table->timestamp('diajukan_at')->nullable()->after('dosen_wali_id');

            $table->char('disetujui_oleh', 36)->nullable()->after('diajukan_at');
            $table->timestamp('disetujui_pada')->nullable()->after('disetujui_oleh');

            $table->char('ditolak_oleh', 36)->nullable()->after('disetujui_pada');
            $table->timestamp('ditolak_pada')->nullable()->after('ditolak_oleh');

            $table->text('catatan_admin')->nullable()->after('ditolak_pada');

            $table->boolean('is_financial_verified')->default(false)->after('catatan_admin');
            $table->char('financial_override_by', 36)->nullable()->after('is_financial_verified');
            $table->text('financial_override_reason')->nullable()->after('financial_override_by');

            $table->integer('total_sks_diambil')->default(0)->after('financial_override_reason');

            $table->char('dispensasi_id', 36)->nullable()->after('total_sks_diambil');

            // Foreign Keys
            $table->foreign('disetujui_oleh')->references('id')->on('users')->nullOnDelete();
            $table->foreign('ditolak_oleh')->references('id')->on('users')->nullOnDelete();
            $table->foreign('financial_override_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('dispensasi_id')->references('id')->on('dispensasi_akademiks')->nullOnDelete();
        });

        // 2. Create tabel krs_status_logs
        Schema::create('krs_status_logs', function (Blueprint $table) {
            $table->id();
            $table->char('krs_id', 36);
            $table->enum('aksi', ['DIAJUKAN', 'DISETUJUI', 'DITOLAK', 'DIBATALKAN', 'DIBUKA_KEMBALI', 'DIUBAH_ADMIN']);
            $table->char('dilakukan_oleh', 36)->nullable();
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Foreign Keys
            $table->foreign('krs_id')->references('id')->on('krs')->cascadeOnDelete();
            $table->foreign('dilakukan_oleh')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('krs_status_logs');

        Schema::table('krs', function (Blueprint $table) {
            $table->dropForeign(['disetujui_oleh']);
            $table->dropForeign(['ditolak_oleh']);
            $table->dropForeign(['financial_override_by']);
            $table->dropForeign(['dispensasi_id']);

            $table->dropColumn([
                'diajukan_at',
                'disetujui_oleh',
                'disetujui_pada',
                'ditolak_oleh',
                'ditolak_pada',
                'catatan_admin',
                'is_financial_verified',
                'financial_override_by',
                'financial_override_reason',
                'total_sks_diambil',
                'dispensasi_id'
            ]);
        });
    }
};
