<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mengubah pembayaran_mahasiswas.tagihan_id dari FK tunggal ke
 * tagihan_mahasiswas menjadi polymorphic (tagihan_type + tagihan_id),
 * supaya satu tabel pembayaran bisa menampung Tagihan Semester DAN
 * Tagihan Non Reguler tanpa membuat sistem pembayaran baru.
 *
 * Integritas referensial yang tadinya dijaga FK sekarang dijaga di
 * PembayaranIntakeService::pastikanTagihanValid() (application-level),
 * karena FK tunggal ke satu tabel tidak lagi bisa dipakai untuk kolom
 * polymorphic.
 *
 * PENTING: jalankan setelah morph map didaftarkan di AppServiceProvider
 * (lihat catatan di ANALISIS-PEMBAYARAN.md) supaya nilai tagihan_type
 * yang dibackfill ('tagihan_mahasiswa') konsisten dengan alias yang
 * dipakai aplikasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran_mahasiswas', function (Blueprint $table) {
            $table->dropForeign('pembayaran_mahasiswas_tagihan_id_foreign');
        });

        Schema::table('pembayaran_mahasiswas', function (Blueprint $table) {
            $table->string('tagihan_type', 100)->nullable()->after('tagihan_id');
        });

        // Backfill: seluruh baris lama pasti tagihan semester, karena
        // sebelum migration ini FK hanya mengizinkan tagihan_mahasiswas.
        DB::table('pembayaran_mahasiswas')->update(['tagihan_type' => 'tagihan_mahasiswa']);

        Schema::table('pembayaran_mahasiswas', function (Blueprint $table) {
            $table->string('tagihan_type', 100)->nullable(false)->change();
            $table->index(['tagihan_type', 'tagihan_id'], 'pembayaran_mahasiswas_tagihan_type_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran_mahasiswas', function (Blueprint $table) {
            $table->dropIndex('pembayaran_mahasiswas_tagihan_type_id_index');
            $table->dropColumn('tagihan_type');
        });

        Schema::table('pembayaran_mahasiswas', function (Blueprint $table) {
            $table->foreign('tagihan_id')
                ->references('id')->on('tagihan_mahasiswas');
        });
    }
};
