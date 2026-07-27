<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lpm_riwayat_peningkatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('standar_id')->constrained('lpm_standars')->cascadeOnDelete();
            $table->unsignedInteger('versi_lama');
            $table->unsignedInteger('versi_baru');
            $table->text('ringkasan_perubahan');
            $table->string('dasar_peningkatan', 255)->nullable()->comment('mis. Hasil AMI, Hasil Monev, Tinjauan Manajemen');
            $table->date('tanggal');
            $table->foreignId('disetujui_oleh_person_id')->nullable()->constrained('ref_person')->nullOnDelete();
            $table->timestamps();

            $table->index(['standar_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lpm_riwayat_peningkatans');
    }
};
