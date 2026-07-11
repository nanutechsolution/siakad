<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class LaporanPiutangService
{
    /**
     * Get Data Piutang Mahasiswa
     */
    public function getPiutang(array $filters): Collection
    {
        $query = DB::table('tagihan_mahasiswas as tm')
            ->join('mahasiswas as m', 'tm.mahasiswa_id', '=', 'm.id')
            ->leftJoin('ref_person as rp', 'm.person_id', '=', 'rp.id') // Join untuk mengambil nama lengkap
            ->join('ref_prodi as p', 'm.prodi_id', '=', 'p.id')
            ->select(
                'tm.id',
                'm.nim',
                'rp.nama_lengkap as nama_mahasiswa',
                'p.nama_prodi',
                'm.angkatan_id as angkatan',
                'tm.total_tagihan',
                'tm.total_bayar',
                'tm.sisa_tagihan',
                'tm.status_bayar',
                'tm.tenggat_waktu',
                DB::raw('DATEDIFF(CURRENT_DATE, tm.tenggat_waktu) as hari_terlambat')
            )
            ->where('tm.sisa_tagihan', '>', 0)
            ->where('tm.status_bayar', '!=', 'LUNAS')
            ->whereNull('tm.deleted_at')
            ->whereNull('m.deleted_at');

        if (!empty($filters['tahun_akademik_id'])) {
            $query->where('tm.tahun_akademik_id', $filters['tahun_akademik_id']);
        }

        if (!empty($filters['prodi_id'])) {
            $query->where('m.prodi_id', $filters['prodi_id']);
        }

        if (!empty($filters['angkatan'])) {
            $query->where('m.angkatan_id', $filters['angkatan']);
        }

        // Pengurutan berdasarkan Prodi dan Angkatan sesuai requirement
        return $query->orderBy('p.nama_prodi')
            ->orderBy('m.angkatan_id', 'desc')
            ->orderBy('rp.nama_lengkap', 'asc')
            ->get();
    }
}
