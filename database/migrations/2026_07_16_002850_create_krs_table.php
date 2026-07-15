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
        Schema::create('krs', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('mahasiswa_id', 36);
            $table->unsignedBigInteger('tahun_akademik_id')->index('krs_tahun_akademik_id_foreign');
            $table->unsignedBigInteger('kelas_id')->nullable()->index('krs_kelas_id_foreign');
            $table->dateTime('tgl_krs')->useCurrent();
            $table->string('status_krs', 20)->default('DRAFT');
            $table->boolean('is_paket_snapshot')->nullable();
            $table->char('dosen_wali_id', 36)->nullable()->index('krs_dosen_wali_id_foreign');
            $table->timestamp('diajukan_at')->nullable();
            $table->char('disetujui_oleh', 36)->nullable()->index('krs_disetujui_oleh_foreign');
            $table->timestamp('disetujui_pada')->nullable();
            $table->char('ditolak_oleh', 36)->nullable()->index('krs_ditolak_oleh_foreign');
            $table->timestamp('ditolak_pada')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->boolean('is_financial_verified')->default(false);
            $table->char('financial_override_by', 36)->nullable()->index('krs_financial_override_by_foreign');
            $table->text('financial_override_reason')->nullable();
            $table->integer('total_sks_diambil')->default(0);
            $table->char('dispensasi_id', 36)->nullable()->index('krs_dispensasi_id_foreign');
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'tahun_akademik_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('krs');
    }
};
