<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * UNIQUE constraint sebagai lapisan proteksi TERAKHIR untuk data finansial
     * dan nilai akademik, di atas segala validasi aplikasi:
     *
     * 1. keuangan_saldos(mahasiswa_id) — mencegah dua baris saldo per mahasiswa.
     *    Sebelumnya hanya index biasa, sehingga dua pembayaran pertama yang
     *    berjalan bersamaan (firstOrCreate / create) bisa membuat dua baris dan
     *    memecah total uang mahasiswa jadi dua.
     *
     * 2. krs_detail_nilai(krs_detail_id, komponen_id) — mencegah komponen nilai
     *    dobel pada input nilai bersamaan, yang berujung nilai akhir (weighted
     *    sum) terhitung dua kali.
     *
     * Kedua duplikasi sudah divalidasi NOL baris di database development sebelum
     * migration ini dibuat, jadi penambahan unique index aman dijalankan.
     * Migration ini hanya menambah index; TIDAK menghapus atau mengubah data.
     */
    public function up(): void
    {
        // Batal lebih dulu bila ternyata ada duplikat yang baru muncul setelah
        // audit — jangan pernah memaksa build index yang akan gagal di tengah.
        if ($this->hasDuplicateSaldo() || $this->hasDuplicateNilai()) {
            throw new \RuntimeException(
                'Terdapat duplikat data pada keuangan_saldos (per mahasiswa) atau krs_detail_nilai '
                . '(per krs_detail_id+komponen_id). Unique index tidak dibuat. '
                . 'Bersihkan duplikat secara manual (pilih record canonical) terlebih dahulu.'
            );
        }

        Schema::table('keuangan_saldos', function (Blueprint $table): void {
            $table->unique('mahasiswa_id', 'keuangan_saldos_mahasiswa_id_unique');
        });

        Schema::table('krs_detail_nilai', function (Blueprint $table): void {
            $table->unique(
                ['krs_detail_id', 'komponen_id'],
                'krs_detail_nilai_detail_komponen_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('krs_detail_nilai', function (Blueprint $table): void {
            $table->dropUnique('krs_detail_nilai_detail_komponen_unique');
        });

        Schema::table('keuangan_saldos', function (Blueprint $table): void {
            $table->dropUnique('keuangan_saldos_mahasiswa_id_unique');
        });
    }

    private function hasDuplicateSaldo(): bool
    {
        return DB::table('keuangan_saldos')
            ->select('mahasiswa_id', DB::raw('COUNT(*) as total'))
            ->groupBy('mahasiswa_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }

    private function hasDuplicateNilai(): bool
    {
        return DB::table('krs_detail_nilai')
            ->select('krs_detail_id', 'komponen_id', DB::raw('COUNT(*) as total'))
            ->groupBy('krs_detail_id', 'komponen_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }
};
