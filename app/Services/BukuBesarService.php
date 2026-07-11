<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class BukuBesarService
{
    /**
     * Get Histori Transaksi Buku Besar (General Ledger) untuk 1 Mahasiswa
     */
    public function getLedger(?string $mahasiswaId): Collection
    {
        if (!$mahasiswaId) {
            return collect();
        }

        return DB::table('keuangan_general_ledgers')
            ->where('mahasiswa_id', $mahasiswaId)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Get Informasi Profil Mahasiswa untuk Header Laporan
     */
    public function getMahasiswaInfo(?string $mahasiswaId): ?object
    {
        if (!$mahasiswaId) {
            return null;
        }

        return DB::table('mahasiswas as m')
            ->join('ref_person as rp', 'm.person_id', '=', 'rp.id')
            ->join('ref_prodi as p', 'm.prodi_id', '=', 'p.id')
            ->where('m.id', $mahasiswaId)
            ->select(
                'm.nim',
                'rp.nama_lengkap as nama_mahasiswa',
                'p.nama_prodi',
                'm.angkatan_id as angkatan'
            )
            ->first();
    }
}