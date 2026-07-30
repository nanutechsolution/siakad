<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Models\LaporanKeuangan\MahasiswaRecord;
use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Laporan #1 — Rekap Tagihan Mahasiswa.
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

        // PERBAIKAN 1: Gunakan alias 'rekap_tagihans' untuk subquery agar tidak
        // bentrok dengan nama tabel fisik 'mahasiswas' yang di-JOIN di dalam query.
        return MahasiswaRecord::query()
            ->fromSub($union, 'rekap_tagihans')
            ->select('rekap_tagihans.*')
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

        // PERBAIKAN 2: t.sisa_tagihan diambil langsung dari Virtual Column MySQL
        return $query->select([
            't.id as id',
            'mahasiswas.id as mahasiswa_id',
            'mahasiswas.nim',
            'p.nama_lengkap',
            'pr.nama_prodi',
            'mahasiswas.angkatan_id',
            DB::raw("'" . TagihanMapQuery::JENIS_SEMESTER . "' as jenis_tagihan"),
            'ta.nama_tahun as periode',
            't.total_tagihan',
            't.total_bayar',
            't.sisa_tagihan',
            't.status_bayar',
        ]);
    }

    private function nonRegulerQuery(array $filters): Builder
    {
        $query = MahasiswaInfoQuery::base()
            ->join('tagihan_non_regulers as t', 't.mahasiswa_id', '=', 'mahasiswas.id')
            ->whereNull('t.deleted_at')
            // PERBAIKAN 3: Karena tagihan_non_regulers TIDAK MEMILIKI tahun_akademik_id/semester,
            // jika user memfilter berdasarkan tahun akademik, query non-reguler diabaikan (kosong).
            ->when($filters['tahun_akademik_id'] ?? null, fn($q) => $q->whereRaw('1 = 0'))
            ->when($filters['semester'] ?? null, fn($q) => $q->whereRaw('1 = 0'));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        // PERBAIKAN 4: Sisa tagihan dihitung manual (t.total_tagihan - t.total_bayar)
        // karena tabel tagihan_non_regulers tidak memiliki virtual column sisa_tagihan.
        return $query->select([
            't.id as id',
            'mahasiswas.id as mahasiswa_id',
            'mahasiswas.nim',
            'p.nama_lengkap',
            'pr.nama_prodi',
            'mahasiswas.angkatan_id',
            DB::raw("'" . TagihanMapQuery::JENIS_NON_REGULER . "' as jenis_tagihan"),
            't.deskripsi as periode',
            't.total_tagihan',
            't.total_bayar',
            DB::raw('(t.total_tagihan - t.total_bayar) as sisa_tagihan'),
            't.status_bayar',
        ]);
    }
}
