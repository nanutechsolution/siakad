<?php

declare(strict_types=1);

namespace App\Models\LaporanKeuangan;

/**
 * Model BACA-SAJA khusus modul Laporan Keuangan, di-map ke tabel
 * `tagihan_non_regulers` yang sudah ada (bukan tabel baru, tidak ada migration).
 *
 * Tujuannya murni teknis: Filament Table butuh Eloquent Builder (bukan
 * Query\Builder biasa) supaya bisa memanggil ->paginate() native di
 * level database (LIMIT/OFFSET sungguhan, bukan mengambil semua baris
 * lalu dipaginate di PHP).
 *
 * Sengaja ditaruh di namespace App\Models\LaporanKeuangan (BUKAN
 * App\Models) supaya TIDAK bentrok dengan Model asli project Anda yang
 * mungkin sudah memetakan tabel yang sama dengan relasi/mutator/scope
 * yang berbeda. Model ini TIDAK dipakai untuk create/update/delete —
 * hanya sebagai anchor query untuk laporan.
 */
class TagihanNonRegulerRecord extends LaporanReadOnlyRecord
{
    protected $table = 'tagihan_non_regulers';

    public $timestamps = false;

    protected $guarded = ['*'];
}
