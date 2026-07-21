<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Models\LaporanKeuangan\MahasiswaRecord;
use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Database\Eloquent\Builder;

/**
 * Laporan #1 — Rekap Tagihan Mahasiswa.
 *
 * PERFORMA: query() TIDAK memanggil ->get(). Filament yang memaginate
 * (LIMIT/OFFSET di database), export yang men-chunk. Union SEMESTER +
 * NON_REGULER tetap bisa dipaginate native karena Laravel mendukung
 * ->paginate() di atas query UNION.
 */
final class RekapTagihanService
{
    public function query(array $filters): Builder
    {
        $jenis = $filters['jenis_tagihan'] ?? null;

        $union = match ($jenis) {
            TagihanMapQuery::JENIS_SEMESTER
            => $this->semesterQuery($filters)->toBase(),

            TagihanMapQuery::JENIS_NON_REGULER
            => $this->nonRegulerQuery($filters)->toBase(),

            default
            => $this->semesterQuery($filters)
                ->toBase()
                ->unionAll(
                    $this->nonRegulerQuery($filters)->toBase()
                ),
        };

        return MahasiswaRecord::query()
            ->fromSub($union, 'mahasiswas')
            ->select('mahasiswas.*')
            ->orderBy('nama_lengkap');
    }

    private function semesterQuery(array $filters): Builder
    {
        $query = MahasiswaInfoQuery::base()
            ->join('tagihan_mahasiswas as t', 't.mahasiswa_id', '=', 'mahasiswas.id')
            ->join('ref_tahun_akademik as ta', 'ta.id', '=', 't.tahun_akademik_id')
            ->whereNull('t.deleted_at')
            ->when($filters['tahun_akademik_id'] ?? null, fn($q, $v) => $q->where('t.tahun_akademik_id', $v))
            ->when($filters['semester'] ?? null, fn($q, $v) => $q->where('ta.semester', $v));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        return $query->selectRaw("
                mahasiswas.id,
                mahasiswa.nim,
                p.nama_lengkap,
                pr.nama_prodi,
                m.angkatan_id,
                '" . TagihanMapQuery::JENIS_SEMESTER . "' as jenis_tagihan,
                ta.nama_tahun as periode,
                t.total_tagihan,
                t.total_bayar,
                t.sisa_tagihan,
                t.status_bayar
            ");
    }

    private function nonRegulerQuery(array $filters): Builder
    {
        $query = MahasiswaInfoQuery::base()
            ->join('tagihan_non_regulers as t', 't.mahasiswa_id', '=', 'mahasiswas.id')
            ->whereNull('t.deleted_at');

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        return $query->selectRaw("
                mahasiswas.id,
                mahasiswas.nim,
                p.nama_lengkap,
                pr.nama_prodi,
                m.angkatan_id,
                '" . TagihanMapQuery::JENIS_NON_REGULER . "' as jenis_tagihan,
                t.deskripsi as periode,
                t.total_tagihan,
                t.total_bayar,
                (t.total_tagihan - t.total_bayar) as sisa_tagihan,
                t.status_bayar
            ");
    }
}
