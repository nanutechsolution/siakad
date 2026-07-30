<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Models\LaporanKeuangan\MahasiswaRecord;
use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use App\Services\LaporanKeuangan\Support\TagihanMapQuery;
use Illuminate\Database\Eloquent\Builder;

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

        // PERBAIKAN 1: Ubah alias subquery dari 'mahasiswas' menjadi 'rekap_subquery'
        // untuk mencegah bentrokan nama tabel internal.
        return MahasiswaRecord::query()
            ->fromSub($union, 'rekap_subquery')
            ->select('rekap_subquery.*')
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

        // PERBAIKAN 2: Gabungkan prefiks 'SEM-' pada ID agar primary key unik
        // PERBAIKAN 3: Bungkus kolom numerik dengan COALESCE
        return $query->selectRaw("
            CONCAT('SEM-', t.id) as id, 
            t.id as original_tagihan_id,
            mahasiswas.id as mahasiswa_id,
            mahasiswas.nim,
            p.nama_lengkap,
            pr.nama_prodi,
            mahasiswas.angkatan_id,
            '" . TagihanMapQuery::JENIS_SEMESTER . "' as jenis_tagihan,
            ta.nama_tahun as periode,
            COALESCE(t.total_tagihan, 0) as total_tagihan,
            COALESCE(t.total_bayar, 0) as total_bayar,
            COALESCE(t.sisa_tagihan, 0) as sisa_tagihan,
            t.status_bayar
        ");
    }

    private function nonRegulerQuery(array $filters): Builder
    {
        $query = MahasiswaInfoQuery::base()
            ->join('tagihan_non_regulers as t', 't.mahasiswa_id', '=', 'mahasiswas.id')
            ->whereNull('t.deleted_at')
            // PERBAIKAN 4: Sesuaikan penanganan filter jika tabel non-reguler punya relasi tahun akademik/periode
            ->when($filters['tahun_akademik_id'] ?? null, function ($q, $v) {
                // Hapus komentar baris berikut jika t.tahun_akademik_id ada di tabel non-reguler:
                // $q->where('t.tahun_akademik_id', $v);
            });

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        // PERBAIKAN 5: Gunakan prefiks 'NON-' untuk ID dan penanganan COALESCE saat pengurangan
        return $query->selectRaw("
            CONCAT('NON-', t.id) as id,
            t.id as original_tagihan_id,
            mahasiswas.id as mahasiswa_id,
            mahasiswas.nim,
            p.nama_lengkap,
            pr.nama_prodi,
            mahasiswas.angkatan_id,
            '" . TagihanMapQuery::JENIS_NON_REGULER . "' as jenis_tagihan,
            t.deskripsi as periode,
            COALESCE(t.total_tagihan, 0) as total_tagihan,
            COALESCE(t.total_bayar, 0) as total_bayar,
            (COALESCE(t.total_tagihan, 0) - COALESCE(t.total_bayar, 0)) as sisa_tagihan,
            t.status_bayar
        ");
    }
}
