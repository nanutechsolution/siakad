<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanKeuanganService
{
    /**
     * Rekap tagihan per Program Studi & Angkatan berdasarkan filter.
     */
    public function getRekapTagihan(array $filters): Collection
    {
        return $this->baseQuery($filters)
            ->groupBy('mahasiswas.prodi_id', 'mahasiswas.angkatan_id', 'ref_prodi.nama_prodi')
            ->selectRaw("
                mahasiswas.prodi_id,
                mahasiswas.angkatan_id as angkatan,
                ref_prodi.nama_prodi,
                COUNT(DISTINCT mahasiswas.id) as total_mahasiswa,
                SUM(tagihan_mahasiswas.total_tagihan) as total_tagihan,
                SUM(tagihan_mahasiswas.total_bayar) as total_bayar,
                SUM(tagihan_mahasiswas.total_tagihan - tagihan_mahasiswas.total_bayar) as total_piutang,
                SUM(CASE WHEN tagihan_mahasiswas.status_bayar = 'LUNAS' THEN 1 ELSE 0 END) as count_lunas,
                SUM(CASE WHEN tagihan_mahasiswas.status_bayar = 'CICIL' THEN 1 ELSE 0 END) as count_cicil,
                SUM(CASE WHEN tagihan_mahasiswas.status_bayar = 'BELUM' THEN 1 ELSE 0 END) as count_belum
            ")
            ->orderBy('ref_prodi.nama_prodi')
            ->orderByDesc('mahasiswas.angkatan_id')
            ->get();
    }

    /**
     * Ringkasan total untuk Stats Overview Widget.
     */
    public function getSummary(array $filters): array
    {
        $result = $this->baseQuery($filters)
            ->selectRaw("
                SUM(tagihan_mahasiswas.total_tagihan) as total_tagihan,
                SUM(tagihan_mahasiswas.total_bayar) as total_bayar,
                SUM(tagihan_mahasiswas.total_tagihan - tagihan_mahasiswas.total_bayar) as total_piutang
            ")
            ->first();

        $totalTagihan = (float) ($result->total_tagihan ?? 0);
        $totalBayar = (float) ($result->total_bayar ?? 0);
        $totalPiutang = (float) ($result->total_piutang ?? 0);

        return [
            'total_tagihan' => $totalTagihan,
            'total_bayar' => $totalBayar,
            'total_piutang' => $totalPiutang,
            'collection_rate' => $totalTagihan > 0
                ? round(($totalBayar / $totalTagihan) * 100, 1)
                : 0,
        ];
    }

    protected function baseQuery(array $filters): Builder
    {
        $query = DB::table('tagihan_mahasiswas')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'tagihan_mahasiswas.mahasiswa_id')
            ->join('ref_prodi', 'ref_prodi.id', '=', 'mahasiswas.prodi_id')
            ->whereNull('tagihan_mahasiswas.deleted_at');

        if (! empty($filters['tahun_akademik_id'])) {
            $query->where('tagihan_mahasiswas.tahun_akademik_id', $filters['tahun_akademik_id']);
        }

        if (! empty($filters['prodi_id'])) {
            $query->where('mahasiswas.prodi_id', $filters['prodi_id']);
        }

        if (! empty($filters['angkatan'])) {
            $query->where('mahasiswas.angkatan_id', $filters['angkatan']);
        }

        if (! empty($filters['tanggal_mulai'])) {
            $query->whereDate('tagihan_mahasiswas.created_at', '>=', $filters['tanggal_mulai']);
        }

        if (! empty($filters['tanggal_akhir'])) {
            $query->whereDate('tagihan_mahasiswas.created_at', '<=', $filters['tanggal_akhir']);
        }

        return $query;
    }
}
