<?php

declare(strict_types=1);

namespace App\Models\LaporanKeuangan;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Base class untuk semua model anchor read-only modul Laporan Keuangan.
 *
 * PENTING: newQuery() di-override untuk SELALU melepas seluruh global
 * scope (->withoutGlobalScopes()). Ini bukan sekadar pemanis — di
 * project ini query kita SELALU meng-alias tabel dasarnya
 * (mis. `FROM mahasiswas AS m`), dan MySQL tidak mengizinkan referensi
 * ke nama tabel asli begitu di-alias. Kalau ada global scope lain di
 * project Anda (mis. dari Filament Shield, package tenant, atau base
 * model kustom) yang otomatis menambahkan kondisi memakai nama tabel
 * ASLI (bukan alias) — contoh error yang pernah muncul:
 * `Unknown column 'mahasiswas.deleted_at'` — query itu akan gagal.
 *
 * Model laporan ini murni baca data, dan kondisi "hanya data yang belum
 * dihapus" SUDAH kita tulis eksplisit sendiri di setiap Service
 * (->whereNull('m.deleted_at') dst) — jadi mematikan global scope di
 * sini aman, tidak menghilangkan proteksi apa pun.
 */
abstract class LaporanReadOnlyRecord extends Model
{
    public function newQuery(): Builder
    {
        return parent::newQuery()->withoutGlobalScopes();
    }
}
