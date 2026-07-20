<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Laporan #9 — Rekap Cicilan.
 *
 * CATATAN: Schema tidak memiliki tabel jadwal cicilan (mis. jumlah cicilan
 * yang direncanakan, tanggal jatuh tempo per cicilan). "Jumlah Cicilan"
 * pada laporan ini adalah nilai turunan = jumlah baris
 * `pembayaran_mahasiswas` berstatus verifikasi FINAL yang terhubung ke
 * tagihan tersebut — bukan data eksplisit dari sistem cicilan formal.
 */
final class CicilanService
{
    public function rows(array $filters): Collection
    {
        $map = TagihanMapQuery::build();

        $query = MahasiswaInfoQuery::base()
            ->joinSub($map, 'tm', fn ($join) => $join->on('tm.mahasiswa_id', '=', 'm.id'))
            ->where('tm.status_bayar', 'CICIL')
            ->when($filters['jenis_tagihan'] ?? null, fn ($q, $v) => $q->where('tm.jenis_tagihan', $v));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        $rows = $query
            ->select([
                'm.id as mahasiswa_id',
                'tm.tagihan_id',
                'm.nim',
                'p.nama_lengkap',
                'pr.nama_prodi',
                'tm.total_tagihan',
                'tm.total_bayar as sudah_dibayar',
                'tm.sisa_tagihan',
                'tm.status_bayar',
            ])
            ->orderBy('p.nama_lengkap')
            ->get();

        $tagihanIds = $rows->pluck('tagihan_id')->all();

        $jumlahCicilanMap = DB::table('pembayaran_mahasiswas as pm')
            ->join('ref_status_verifikasi_pembayaran as sv', 'sv.id', '=', 'pm.status_verifikasi_id')
            ->whereNull('pm.deleted_at')
            ->where('sv.is_final', true)
            ->whereIn('pm.tagihan_id', $tagihanIds)
            ->select('pm.tagihan_id')
            ->selectRaw('COUNT(*) as jumlah_cicilan')
            ->groupBy('pm.tagihan_id')
            ->get()
            ->keyBy('tagihan_id');

        return $rows->map(function (\stdClass $row) use ($jumlahCicilanMap) {
            $row->jumlah_cicilan = (int) ($jumlahCicilanMap->get($row->tagihan_id)->jumlah_cicilan ?? 0);

            return $row;
        });
    }
}