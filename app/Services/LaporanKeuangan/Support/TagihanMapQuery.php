<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan\Support;

use App\Models\LaporanKeuangan\TagihanMahasiswaRecord;
use App\Models\LaporanKeuangan\TagihanNonRegulerRecord;
use Illuminate\Database\Eloquent\Builder;

/**
 * Menyatukan `tagihan_mahasiswas` (SEMESTER) dan `tagihan_non_regulers`
 * (NON_REGULER) menjadi satu subquery (UNION ALL) yang bisa di-join
 * seperti tabel biasa (alias `tm`).
 *
 * PERUBAHAN PERFORMA: `hari_keterlambatan` sekarang dihitung DI SQL
 * (GREATEST(DATEDIFF(...), 0)), bukan di PHP setelah ->get(). Ini
 * penting justru KARENA sekarang query dipaginate native — kalau
 * komputasi ini masih di PHP, ia hanya jalan untuk baris yang benar-benar
 * diambil (aman), tapi menaruhnya di SQL membuat kolom ini bisa
 * di-ORDER BY / dipakai kondisi lanjutan tanpa fetch tambahan.
 *
 * CATATAN: join ke `pembayaran_mahasiswas` di service lain dilakukan
 * murni berdasarkan `tagihan_id` (UUID), TIDAK bergantung pada isi
 * `tagihan_type` (lihat catatan lengkap di versi sebelumnya) — ini tetap
 * berlaku sama di versi refactor ini.
 */
final class TagihanMapQuery
{
    public const JENIS_SEMESTER = 'SEMESTER';

    public const JENIS_NON_REGULER = 'NON_REGULER';

    public static function build(): Builder
    {
        $semester = TagihanMahasiswaRecord::query()
            ->whereNull('deleted_at')
            ->selectRaw("
                id as tagihan_id,
                mahasiswa_id,
                tahun_akademik_id,
                '" . self::JENIS_SEMESTER . "' as jenis_tagihan,
                total_tagihan,
                total_bayar,
                sisa_tagihan,
                status_bayar,
                tenggat_waktu,
                kode_transaksi,
                deskripsi,
                GREATEST(DATEDIFF(CURDATE(), tenggat_waktu), 0) as hari_keterlambatan
            ");

        $nonReguler = TagihanNonRegulerRecord::query()
            ->whereNull('deleted_at')
            ->selectRaw("
                id as tagihan_id,
                mahasiswa_id,
                NULL as tahun_akademik_id,
                '" . self::JENIS_NON_REGULER . "' as jenis_tagihan,
                total_tagihan,
                total_bayar,
                (total_tagihan - total_bayar) as sisa_tagihan,
                status_bayar,
                tenggat_waktu,
                kode_transaksi,
                deskripsi,
                GREATEST(DATEDIFF(CURDATE(), tenggat_waktu), 0) as hari_keterlambatan
            ");

        return $semester->unionAll($nonReguler);
    }
}
