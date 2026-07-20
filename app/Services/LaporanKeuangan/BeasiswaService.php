<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Laporan #8 — Rekap Beasiswa.
 *
 * PENTING (sudah dikonfirmasi pada tahap analisa): tidak ada foreign key
 * yang menghubungkan langsung baris `tagihan_mahasiswas_details` dengan
 * beasiswa penyebabnya. Kolom "Potongan Tagihan" pada laporan ini adalah
 * ESTIMASI, dihitung dari aturan `keuangan_beasiswa_details` terhadap
 * tarif di `keuangan_detail_tarif` (dicocokkan via skema tarif aktif
 * mahasiswa: angkatan + prodi + program). Ini BUKAN hasil telusur
 * transaksi tagihan riil — beri catatan ini juga di UI.
 */
final class BeasiswaService
{
    public function rows(array $filters): Collection
    {
        $query = MahasiswaInfoQuery::base()
            ->join('keuangan_mahasiswa_beasiswas as mb', 'mb.mahasiswa_id', '=', 'm.id')
            ->join('keuangan_master_beasiswas as mbe', 'mbe.id', '=', 'mb.beasiswa_id')
            ->join('ref_tahun_akademik as ta_mulai', 'ta_mulai.id', '=', 'mb.tahun_akademik_mulai_id')
            ->leftJoin('ref_tahun_akademik as ta_akhir', 'ta_akhir.id', '=', 'mb.tahun_akademik_akhir_id')
            ->when($filters['beasiswa_id'] ?? null, fn ($q, $v) => $q->where('mb.beasiswa_id', $v))
            ->when(! ($filters['tampilkan_nonaktif'] ?? false), fn ($q) => $q->where('mb.is_active', true));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        $rows = $query
            ->select([
                'mb.id as mahasiswa_beasiswa_id',
                'mb.beasiswa_id',
                'm.id as mahasiswa_id',
                'p.nama_lengkap',
                'mbe.nama_beasiswa',
                'mbe.kategori',
                'ta_mulai.nama_tahun as periode_mulai',
                'ta_akhir.nama_tahun as periode_akhir',
                'mb.is_active',
            ])
            ->orderBy('p.nama_lengkap')
            ->get();

        // Catatan performa: perhitungan estimasi dilakukan per baris (bukan
        // agregasi tunggal) karena setiap mahasiswa bisa punya skema tarif
        // berbeda. Untuk jumlah penerima beasiswa yang wajar (puluhan-ratusan
        // per angkatan), ini cukup cepat; jika data sangat besar,
        // pertimbangkan pre-agregasi per (angkatan, prodi, program, beasiswa).
        return $rows->map(function (\stdClass $row) {
            $row->estimasi_potongan = $this->estimasiPotongan((string) $row->mahasiswa_id, (int) $row->beasiswa_id);

            return $row;
        });
    }

    private function estimasiPotongan(string $mahasiswaId, int $beasiswaId): float
    {
        $mahasiswa = DB::table('mahasiswas')
            ->where('id', $mahasiswaId)
            ->select('angkatan_id', 'prodi_id', 'program_id')
            ->first();

        if ($mahasiswa === null || $mahasiswa->program_id === null) {
            return 0.0;
        }

        $skemaTarifId = DB::table('keuangan_skema_tarif')
            ->where('angkatan_id', $mahasiswa->angkatan_id)
            ->where('prodi_id', $mahasiswa->prodi_id)
            ->where('program_kelas_id', $mahasiswa->program_id)
            ->where('is_active', true)
            ->value('id');

        if ($skemaTarifId === null) {
            return 0.0;
        }

        $details = DB::table('keuangan_beasiswa_details as bd')
            ->join('keuangan_detail_tarif as dt', function ($join) use ($skemaTarifId) {
                $join->on('dt.komponen_biaya_id', '=', 'bd.komponen_biaya_id')
                    ->where('dt.skema_tarif_id', '=', $skemaTarifId);
            })
            ->where('bd.beasiswa_id', $beasiswaId)
            ->select('bd.tipe_diskon', 'bd.nilai_diskon', 'dt.nominal as tarif_nominal')
            ->get();

        return (float) $details->sum(function (\stdClass $d) {
            return $d->tipe_diskon === 'PERSENTASE'
                ? ($d->tarif_nominal * ($d->nilai_diskon / 100))
                : $d->nilai_diskon;
        });
    }
}