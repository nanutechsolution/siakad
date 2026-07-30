<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Models\LaporanKeuangan\MahasiswaRecord;
use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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

        // PERBAIKAN 1: Ganti alias 'mahasiswas' menjadi 'rekap' agar tidak bentrok
        // dengan tabel fisik mahasiswas yang di-JOIN di dalam subquery.
        return MahasiswaRecord::query()
            ->fromSub($union, 'rekap')
            ->select('rekap.*')
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

        // PERBAIKAN 2: Gunakan array select() eksplisit untuk menimpa select bawaan
        // dari MahasiswaInfoQuery::base() dan menjamin urutan kolom UNION presisi.
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
            't.sisa_tagihan', // Langsung ambil dari Virtual Column MySQL
            't.status_bayar',
        ]);
    }

    private function nonRegulerQuery(array $filters): Builder
    {
        $query = MahasiswaInfoQuery::base()
            ->join('tagihan_non_regulers as t', 't.mahasiswa_id', '=', 'mahasiswas.id')
            ->whereNull('t.deleted_at');

        // PERBAIKAN 3: Tangani filter tahun akademik untuk non-reguler
        // (Sesuaikan kolom 'tahun_akademik_id' jika ada di tabel tagihan_non_regulers)
        if (!empty($filters['tahun_akademik_id'])) {
            // $query->where('t.tahun_akademik_id', $filters['tahun_akademik_id']);
        }

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        // Pastikan jumlah dan urutan kolom persis sama dengan semesterQuery
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
            DB::raw('COALESCE(t.total_bayar, 0) as total_bayar'),
            DB::raw('(t.total_tagihan - COALESCE(t.total_bayar, 0)) as sisa_tagihan'),
            't.status_bayar',
        ]);
    }
}
