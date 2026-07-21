<?php

declare(strict_types=1);

namespace App\Services\LaporanKeuangan;

use App\Services\LaporanKeuangan\Support\MahasiswaInfoQuery;
use Illuminate\Database\Eloquent\Builder;

/**
 * Laporan #8 — Rekap Beasiswa.
 *
 * PERFORMA: versi sebelumnya menghitung estimasi potongan dengan query
 * TAMBAHAN per baris di PHP (rows->map(...)) — N+1 murni. Sekarang
 * dipindah jadi correlated subquery bersarang di SELECT. Ini query yang
 * lebih "berat" per baris dibanding laporan lain, TAPI karena hanya
 * dieksekusi untuk baris pada halaman aktif (paginate) atau dalam satu
 * chunk export (bukan seluruh dataset sekaligus), dan jumlah penerima
 * beasiswa secara alami jauh lebih kecil dari total mahasiswa, ini masih
 * aman. Kalau di kemudian hari data beasiswa jadi sangat besar, opsi
 * lanjutan: pre-agregasi ke tabel ringkasan terpisah (materialized).
 *
 * PENTING (sudah dikonfirmasi pada tahap analisa): tidak ada foreign key
 * yang menghubungkan langsung `tagihan_mahasiswas_details` dengan
 * beasiswa penyebabnya — kolom "Potongan Tagihan" adalah ESTIMASI dari
 * aturan `keuangan_beasiswa_details` × `keuangan_detail_tarif` pada
 * skema tarif aktif mahasiswa, BUKAN hasil telusur transaksi riil.
 */
final class BeasiswaService
{
    public function query(array $filters): Builder
    {
        $query = MahasiswaInfoQuery::base()
            ->join('keuangan_mahasiswa_beasiswas as mb', 'mb.mahasiswa_id', '=', 'm.id')
            ->join('keuangan_master_beasiswas as mbe', 'mbe.id', '=', 'mb.beasiswa_id')
            ->join('ref_tahun_akademik as ta_mulai', 'ta_mulai.id', '=', 'mb.tahun_akademik_mulai_id')
            ->leftJoin('ref_tahun_akademik as ta_akhir', 'ta_akhir.id', '=', 'mb.tahun_akademik_akhir_id')
            ->when($filters['beasiswa_id'] ?? null, fn ($q, $v) => $q->where('mb.beasiswa_id', $v))
            ->when(! ($filters['tampilkan_nonaktif'] ?? false), fn ($q) => $q->where('mb.is_active', true));

        $query = MahasiswaInfoQuery::applyFilters($query, $filters);

        return $query
            ->orderBy('p.nama_lengkap')
            ->selectRaw("
                m.id as mahasiswa_id,
                p.nama_lengkap,
                mbe.nama_beasiswa,
                mbe.kategori,
                ta_mulai.nama_tahun as periode_mulai,
                ta_akhir.nama_tahun as periode_akhir,
                mb.is_active,
                (
                    SELECT COALESCE(SUM(
                        CASE WHEN bd.tipe_diskon = 'PERSENTASE'
                             THEN dt.nominal * (bd.nilai_diskon / 100)
                             ELSE bd.nilai_diskon
                        END
                    ), 0)
                    FROM keuangan_beasiswa_details bd
                    JOIN keuangan_detail_tarif dt
                        ON dt.komponen_biaya_id = bd.komponen_biaya_id
                       AND dt.skema_tarif_id = (
                            SELECT st.id FROM keuangan_skema_tarif st
                            WHERE st.angkatan_id = m.angkatan_id
                              AND st.prodi_id = m.prodi_id
                              AND st.program_kelas_id = m.program_id
                              AND st.is_active = 1
                            LIMIT 1
                       )
                    WHERE bd.beasiswa_id = mb.beasiswa_id
                ) as estimasi_potongan
            ");
    }
}
