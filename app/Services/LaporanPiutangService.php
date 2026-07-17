<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanPiutangService
{
    /**
     * Piutang mahasiswa, PAGINATED di level database (bukan di-load
     * semua lalu dipotong di PHP seperti versi sebelumnya). Menggabungkan
     * tagihan_mahasiswas (semester) dan tagihan_non_regulers lewat UNION
     * ALL, supaya laporan piutang benar-benar mencerminkan seluruh
     * kewajiban mahasiswa, bukan cuma tagihan semester.
     */
    public function getPiutang(array $filters, ?string $search, int $page, int $perPage): LengthAwarePaginatorContract
    {
        $gabungan = $this->buildGabunganQuery($filters, $search);

        if ($gabungan === null) {
            return new LengthAwarePaginator([], 0, $perPage, $page);
        }

        $paginator = DB::query()
            ->fromSub($gabungan, 'piutang')
            ->orderBy('nama_prodi')
            ->orderBy('angkatan', 'desc')
            ->orderBy('nama_mahasiswa')
            ->paginate($perPage, ['*'], 'page', $page);

        return $paginator->through(fn ($row) => (array) $row);
    }

    /**
     * Versi TANPA paginasi, khusus untuk export (Excel/PDF) yang memang
     * butuh seluruh baris sesuai filter, bukan cuma satu halaman.
     */
    public function getPiutangUntukExport(array $filters, ?string $search = null): Collection
    {
        $gabungan = $this->buildGabunganQuery($filters, $search);

        if ($gabungan === null) {
            return collect();
        }

        return $gabungan
            ->orderBy('nama_prodi')
            ->orderBy('angkatan', 'desc')
            ->orderBy('nama_mahasiswa')
            ->get()
            ->map(fn ($row) => (array) $row);
    }

    private function buildGabunganQuery(array $filters, ?string $search): ?QueryBuilder
    {
        $jenis = $filters['jenis_tagihan'] ?? null;
        $sertakanSemester = empty($jenis) || $jenis === 'SEMESTER';
        $sertakanNonReguler = empty($jenis) || $jenis === 'NON_REGULER';

        $queries = [];

        if ($sertakanSemester) {
            $queries[] = $this->querySemester($filters, $search);
        }

        if ($sertakanNonReguler) {
            $queries[] = $this->queryNonReguler($filters, $search);
        }

        if (empty($queries)) {
            return null;
        }

        /** @var QueryBuilder $gabungan */
        $gabungan = array_shift($queries);

        foreach ($queries as $query) {
            $gabungan->unionAll($query);
        }

        return $gabungan;
    }

    private function querySemester(array $filters, ?string $search): QueryBuilder
    {
        $query = DB::table('tagihan_mahasiswas as tm')
            ->join('mahasiswas as m', 'tm.mahasiswa_id', '=', 'm.id')
            ->leftJoin('ref_person as rp', 'm.person_id', '=', 'rp.id')
            ->join('ref_prodi as p', 'm.prodi_id', '=', 'p.id')
            ->select([
                'tm.id',
                'm.nim',
                'rp.nama_lengkap as nama_mahasiswa',
                'p.nama_prodi',
                'm.angkatan_id as angkatan',
                DB::raw("'SEMESTER' as jenis_tagihan"),
                'tm.deskripsi',
                'tm.total_tagihan',
                'tm.total_bayar',
                DB::raw('(tm.total_tagihan - tm.total_bayar) as sisa_tagihan'),
                'tm.status_bayar',
                'tm.tenggat_waktu',
                DB::raw('DATEDIFF(CURRENT_DATE, tm.tenggat_waktu) as hari_terlambat'),
            ])
            ->whereColumn('tm.total_tagihan', '>', 'tm.total_bayar')
            ->where('tm.status_bayar', '!=', 'LUNAS')
            ->whereNull('tm.deleted_at')
            ->whereNull('m.deleted_at');

        if (!empty($filters['tahun_akademik_id'])) {
            $query->where('tm.tahun_akademik_id', $filters['tahun_akademik_id']);
        }

        $this->terapkanFilterUmum($query, $filters, $search, 'm', 'rp');

        return $query;
    }

    private function queryNonReguler(array $filters, ?string $search): QueryBuilder
    {
        $query = DB::table('tagihan_non_regulers as tnr')
            ->join('mahasiswas as m', 'tnr.mahasiswa_id', '=', 'm.id')
            ->leftJoin('ref_person as rp', 'm.person_id', '=', 'rp.id')
            ->join('ref_prodi as p', 'm.prodi_id', '=', 'p.id')
            ->select([
                'tnr.id',
                'm.nim',
                'rp.nama_lengkap as nama_mahasiswa',
                'p.nama_prodi',
                'm.angkatan_id as angkatan',
                DB::raw("'NON_REGULER' as jenis_tagihan"),
                'tnr.deskripsi',
                'tnr.total_tagihan',
                'tnr.total_bayar',
                DB::raw('(tnr.total_tagihan - tnr.total_bayar) as sisa_tagihan'),
                'tnr.status_bayar',
                'tnr.tenggat_waktu',
                DB::raw('DATEDIFF(CURRENT_DATE, tnr.tenggat_waktu) as hari_terlambat'),
            ])
            ->whereColumn('tnr.total_tagihan', '>', 'tnr.total_bayar')
            ->where('tnr.status_bayar', '!=', 'LUNAS')
            ->whereNull('tnr.deleted_at')
            ->whereNull('m.deleted_at');

        // tahun_akademik_id SENGAJA TIDAK diterapkan di sini — tagihan non
        // reguler tidak terikat semester (lihat ANALISIS.md tagihan non
        // reguler), jadi tetap tampil apa pun filter tahun akademiknya.

        $this->terapkanFilterUmum($query, $filters, $search, 'm', 'rp');

        return $query;
    }

    private function terapkanFilterUmum(QueryBuilder $query, array $filters, ?string $search, string $mahasiswaAlias, string $refPersonAlias): void
    {
        if (!empty($filters['prodi_id'])) {
            $query->where("{$mahasiswaAlias}.prodi_id", $filters['prodi_id']);
        }

        if (!empty($filters['angkatan'])) {
            $query->where("{$mahasiswaAlias}.angkatan_id", $filters['angkatan']);
        }

        if (!empty($search)) {
            $query->where(function (QueryBuilder $q) use ($search, $mahasiswaAlias, $refPersonAlias) {
                $q->where("{$mahasiswaAlias}.nim", 'like', "%{$search}%")
                    ->orWhere("{$refPersonAlias}.nama_lengkap", 'like', "%{$search}%");
            });
        }
    }
}