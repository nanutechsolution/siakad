<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OPSIONAL — TIDAK WAJIB DIJALANKAN.
 *
 * Lihat ANALISIS.md poin 2. Jalankan migration ini HANYA jika Anda ingin
 * mengunci aturan "1 komponen biaya hanya boleh muncul 1x per tagihan non
 * reguler", meniru constraint `unik_tagihan_komponen` pada
 * tagihan_mahasiswas_details.
 *
 * Jangan jalankan jika Anda memang butuh komponen biaya yang sama muncul
 * berkali-kali dalam satu tagihan (mis. 2x biaya cetak revisi).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihan_non_reguler_details', function (Blueprint $table) {
            $table->unique(['tagihan_id', 'komponen_biaya_id'], 'unik_tagihan_nr_komponen');
        });
    }

    public function down(): void
    {
        Schema::table('tagihan_non_reguler_details', function (Blueprint $table) {
            $table->dropUnique('unik_tagihan_nr_komponen');
        });
    }
};