<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjamin 1 mahasiswa hanya boleh punya 1 row tagihan paket semester
     * (tagihan_mahasiswas) per tahun_akademik_id. Ini fix definitif untuk
     * race condition di GenerateTagihanJob -- exists()-check di level
     * aplikasi tidak cukup untuk mencegah duplikat kalau job/request
     * ke-trigger dua kali (double click, retry, dsb).
     *
     * Tagihan susulan/denda/her-registrasi/sidang TIDAK disimpan di tabel
     * ini (keputusan arsitektur: tabel terpisah, akan dibuat terpisah saat
     * fitur tagihan susulan mulai dikerjakan), jadi constraint ini aman
     * dipasang tanpa mengganggu kebutuhan tagihan susulan di masa depan.
     *
     * PENTING: jalankan query pengecekan duplikat berikut dulu sebelum
     * migrate, migrasi ini akan GAGAL kalau masih ada data duplikat:
     *
     *   SELECT mahasiswa_id, tahun_akademik_id, COUNT(*) AS jumlah
     *   FROM tagihan_mahasiswas
     *   GROUP BY mahasiswa_id, tahun_akademik_id
     *   HAVING COUNT(*) > 1;
     */
    public function up(): void
    {
        Schema::table('tagihan_mahasiswas', function (Blueprint $table) {
            $table->unique(
                ['mahasiswa_id', 'tahun_akademik_id'],
                'tagihan_mahasiswas_mhs_tahun_akademik_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('tagihan_mahasiswas', function (Blueprint $table) {
            $table->dropUnique('tagihan_mahasiswas_mhs_tahun_akademik_unique');
        });
    }
};
