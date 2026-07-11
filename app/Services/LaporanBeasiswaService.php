<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class LaporanBeasiswaService
{
    /**
     * Get Data Rekap Mahasiswa Penerima Beasiswa
     */
    public function getRekap(array $filters): Collection
    {
        $tahunAkademikId = $filters['tahun_akademik_id'] ?? DB::table('ref_tahun_akademik')->where('is_active', 1)->value('id');
        $safeTahunAkademikId = (int) $tahunAkademikId;

        $query = DB::table('keuangan_mahasiswa_beasiswas as kmb')
            ->join('keuangan_master_beasiswas as mb', 'kmb.beasiswa_id', '=', 'mb.id')
            ->join('mahasiswas as m', 'kmb.mahasiswa_id', '=', 'm.id')
            ->leftJoin('ref_person as rp', 'm.person_id', '=', 'rp.id')
            ->join('ref_prodi as p', 'm.prodi_id', '=', 'p.id')
            ->select(
                'm.nim',
                'rp.nama_lengkap as nama_mahasiswa',
                'p.nama_prodi',
                'm.angkatan_id as angkatan',
                'mb.nama_beasiswa',
                'mb.kategori',
                'kmb.nomor_sk'
            )
            ->where('kmb.is_active', 1)
            ->whereNull('m.deleted_at')
            ->where('kmb.tahun_akademik_mulai_id', '<=', $safeTahunAkademikId)
            ->where(function($q) use ($safeTahunAkademikId) {
                $q->where('kmb.tahun_akademik_akhir_id', '>=', $safeTahunAkademikId)
                  ->orWhereNull('kmb.tahun_akademik_akhir_id');
            });

        // Subquery untuk menghitung total riil diskon (potongan) pada periode akademik terpilih
        $query->addSelect(DB::raw("
            COALESCE((
                SELECT SUM(tmd.nominal_diskon)
                FROM tagihan_mahasiswas_details tmd
                JOIN tagihan_mahasiswas tm ON tm.id = tmd.tagihan_id
                WHERE tm.mahasiswa_id = m.id 
                  AND tm.tahun_akademik_id = {$safeTahunAkademikId}
                  AND tm.deleted_at IS NULL
            ), 0) as total_potongan
        "));

        if (!empty($filters['prodi_id'])) {
            $query->where('m.prodi_id', $filters['prodi_id']);
        }
        
        if (!empty($filters['beasiswa_id'])) {
            $query->where('kmb.beasiswa_id', $filters['beasiswa_id']);
        }

        return $query->orderBy('mb.nama_beasiswa')
            ->orderBy('p.nama_prodi')
            ->orderBy('m.angkatan_id', 'desc')
            ->orderBy('rp.nama_lengkap')
            ->get();
    }
}