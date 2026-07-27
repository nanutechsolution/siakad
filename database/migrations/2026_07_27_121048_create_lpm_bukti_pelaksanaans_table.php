<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * NOTE: satu baris bukti pelaksanaan menempel ke SALAH SATU dari dua induk
 * (nullable & saling eksklusif secara praktik):
 * - iku_target_id: bukti realisasi/pelaksanaan untuk mencapai target IKU
 *   (tahap "Pelaksanaan" PPEPP) — mengganti pola lama `file_bukti_path`
 *   tunggal di lpm_iku_targets yang cuma bisa 1 file per target.
 * - finding_id: bukti pelaksanaan tindak lanjut/corrective action atas
 *   temuan AMI (melengkapi lpm_ami_evidences yang sifatnya bukti audit,
 *   bukan bukti pelaksanaan perbaikan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_bukti_pelaksanaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iku_target_id')->nullable()->constrained('lpm_iku_targets')->cascadeOnDelete();
            $table->foreignId('finding_id')->nullable()->constrained('lpm_ami_findings')->cascadeOnDelete();
            $table->foreignId('unit_kerja_id')->constrained('lpm_unit_kerjas')->cascadeOnDelete();
            $table->string('judul', 255);
            $table->string('file_path', 255);
            $table->text('keterangan')->nullable();
            $table->foreignId('uploaded_by_person_id')->nullable()->constrained('ref_person')->nullOnDelete();
            $table->date('tanggal');
            $table->timestamps();

            $table->index(['iku_target_id', 'finding_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_bukti_pelaksanaans');
    }
};
