<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Menyatukan `tagihan_mahasiswas` (SEMESTER) dan `tagihan_non_regulers`
 * (NON_REGULER) menjadi satu peta (tagihan_id, mahasiswa_id,
 * tahun_akademik_id, jenis_tagihan) yang bisa dijadikan subquery join.
 *
 * CATATAN PENTING: `pembayaran_mahasiswas` menyimpan referensi tagihan
 * secara polymorphic (`tagihan_type` + `tagihan_id`). Kita sengaja TIDAK
 * bergantung pada isi string `tagihan_type` (bisa berupa FQCN model atau
 * morph-alias custom, tidak diketahui dari schema saja). Join dilakukan
 * murni berdasarkan `tagihan_id` (UUID), yang secara praktik tidak pernah
 * bentrok antar dua tabel sumber tagihan.
 *
 * Jika di kemudian hari ternyata ada kebutuhan strict-check terhadap
 * `tagihan_type`, tambahkan kondisi tambahan pada query yang meng-consume
 * map ini.
 */
final class TagihanMapQuery
{
    public const JENIS_SEMESTER = 'SEMESTER';

    public const JENIS_NON_REGULER = 'NON_REGULER';

    public static function build(): Builder
    {
        $semester = DB::table('tagihan_mahasiswas')
            ->whereNull('deleted_at')
            ->select([
                'id as tagihan_id',
                'mahasiswa_id',
                'tahun_akademik_id',
                DB::raw("'" . self::JENIS_SEMESTER . "' as jenis_tagihan"),
                'total_tagihan',
                'total_bayar',
                'sisa_tagihan',
                'status_bayar',
                'tenggat_waktu',
                'kode_transaksi',
                'deskripsi',
            ]);

        $nonReguler = DB::table('tagihan_non_regulers')
            ->whereNull('deleted_at')
            ->select([
                'id as tagihan_id',
                'mahasiswa_id',
                DB::raw('NULL as tahun_akademik_id'),
                DB::raw("'" . self::JENIS_NON_REGULER . "' as jenis_tagihan"),
                'total_tagihan',
                'total_bayar',
                DB::raw('(total_tagihan - total_bayar) as sisa_tagihan'),
                'status_bayar',
                'tenggat_waktu',
                'kode_transaksi',
                'deskripsi',
            ]);

        return $semester->unionAll($nonReguler);
    }
}
