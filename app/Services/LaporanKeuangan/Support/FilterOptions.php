<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan\Support;

use Illuminate\Support\Facades\DB;

/**
 * Sumber opsi dropdown filter, terpusat supaya tidak duplikasi query di
 * setiap Page. Semua method mengembalikan array [id => label] siap pakai
 * untuk Filament Select::options().
 */
final class FilterOptions
{
    public static function tahunAkademik(): array
    {
        return DB::table('ref_tahun_akademik')
            ->orderByDesc('id')
            ->pluck('nama_tahun', 'id')
            ->all();
    }

    public static function fakultas(): array
    {
        return DB::table('ref_fakultas')
            ->orderBy('nama_fakultas')
            ->pluck('nama_fakultas', 'id')
            ->all();
    }

    public static function prodi(?int $fakultasId = null): array
    {
        return DB::table('ref_prodi')
            ->when($fakultasId, fn($q, $v) => $q->where('fakultas_id', $v))
            ->orderBy('nama_prodi')
            ->pluck('nama_prodi', 'id')
            ->all();
    }

    public static function angkatan(): array
    {
        return DB::table('ref_angkatan')
            ->orderByDesc('tahun')
            ->pluck('id_tahun', 'id_tahun')
            ->all();
    }

    public static function statusVerifikasiPembayaran(): array
    {
        return DB::table('ref_status_verifikasi_pembayaran')
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->all();
    }

    public static function masterBeasiswa(): array
    {
        return DB::table('keuangan_master_beasiswas')
            ->orderBy('nama_beasiswa')
            ->pluck('nama_beasiswa', 'id')
            ->all();
    }

    public static function jenisTagihan(): array
    {
        return [
            TagihanMapQuery::JENIS_SEMESTER => 'Semester',
            TagihanMapQuery::JENIS_NON_REGULER => 'Non-Reguler',
        ];
    }

    /** Untuk Select::searchable() pada Laporan Saldo (pilih 1 mahasiswa). */
    public static function searchMahasiswa(string $search): array
    {
        return DB::table('mahasiswas as m')
            ->join('ref_person as p', 'p.id', '=', 'm.person_id')
            ->whereNull('m.deleted_at')
            ->where(function ($q) use ($search) {
                $q->where('m.nim', 'like', "%{$search}%")
                    ->orWhere('p.nama_lengkap', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get(['m.id', 'm.nim', 'p.nama_lengkap'])
            ->mapWithKeys(fn($row) => [$row->id => "{$row->nim} — {$row->nama_lengkap}"])
            ->all();
    }

    public static function mahasiswaLabel(string $id): ?string
    {
        $row = DB::table('mahasiswas as m')
            ->join('ref_person as p', 'p.id', '=', 'm.person_id')
            ->where('m.id', $id)
            ->first(['m.nim', 'p.nama_lengkap']);

        return $row ? "{$row->nim} — {$row->nama_lengkap}" : null;
    }
}
