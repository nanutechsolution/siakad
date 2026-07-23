<?php

namespace App\Services\Mahasiswa;

use App\Models\Mahasiswa;
use App\Models\RefSkalaNilai;
use App\Models\RiwayatStatusMahasiswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class NilaiAkademikService
{
    /**
     * Satu-satunya titik masuk untuk resolve mahasiswa dari user yang
     * sedang login. SEMUA halaman/resource di modul ini wajib memanggil
     * method ini -- jangan pernah menerima mahasiswa_id dari parameter
     * URL/request.
     */
    public function mahasiswaLogin(): Mahasiswa
    {
        $mahasiswa = Auth::user()?->person?->mahasiswa;

        if (! $mahasiswa) {
            throw new AccessDeniedHttpException(
                'Akun Anda tidak terhubung dengan data mahasiswa manapun.'
            );
        }

        return $mahasiswa;
    }

    /**
     * Query dasar untuk resource "Nilai Saya".
     * Sudah di-scope ke mahasiswa login + eager load relasi yang
     * dibutuhkan tabel agar tidak N+1.
     */
    public function nilaiSayaQuery(): Builder
    {
        $mahasiswa = $this->mahasiswaLogin();

        return \App\Models\KrsDetail::query()
            ->nilaiTayang()
            ->milikMahasiswa($mahasiswa->id)
            ->with([
                'krs.tahunAkademik',
                'mataKuliah',
                'jadwalKuliah.dosenPengampu.dosen.person',
                'nilaiKomponen.komponen',
            ]);
    }

    /** Daftar tahun akademik yang punya riwayat KRS mahasiswa (untuk filter KHS). */
    public function daftarTahunAkademikMahasiswa(Mahasiswa $mahasiswa): Collection
    {
        return $mahasiswa->krs()
            ->with('tahunAkademik')
            ->get()
            ->pluck('tahunAkademik')
            ->filter()
            ->unique('id')
            ->sortByDesc('id')
            ->values();
    }

    /**
     * Data lengkap KHS untuk satu tahun akademik.
     * Ringkasan IPS/IPK diambil dari riwayat_status_mahasiswas (data resmi),
     * BUKAN dihitung ulang di aplikasi -- sesuai keputusan bisnis.
     */
    public function khsData(Mahasiswa $mahasiswa, int $tahunAkademikId): array
    {
        $ringkasan = RiwayatStatusMahasiswa::query()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $tahunAkademikId)
            ->first();

        $detailNilai = \App\Models\KrsDetail::query()
            ->nilaiTayang()
            ->milikMahasiswa($mahasiswa->id)
            ->whereHas('krs', fn(Builder $q) => $q->where('tahun_akademik_id', $tahunAkademikId))
            ->with(['mataKuliah'])
            ->get();

        return [
            'ringkasan' => $ringkasan,
            'mata_kuliah' => $detailNilai,
            'jumlah_mk' => $detailNilai->count(),
        ];
    }

    /**
     * Data Transkrip Sementara: seluruh riwayat akademik dari
     * akademik_transkrip (nilai final/terbaik per mata kuliah) + IPK &
     * total SKS terakhir dari riwayat_status_mahasiswas.
     */
    public function transkripData(Mahasiswa $mahasiswa): array
    {
        $riwayat = $mahasiswa->transkrip()
            ->with('mataKuliah')
            ->get()
            ->sortBy(fn($row) => $row->mataKuliah?->kode_mk);
        $statusTerakhir = $mahasiswa->riwayatStatus()
            ->orderByDesc('tahun_akademik_id')
            ->first();

        $petaLulus = $this->petaHurufLulus();

        return [
            'mata_kuliah' => $riwayat,
            'total_sks_ditempuh' => $riwayat->sum('sks_diakui'),
            'total_sks_lulus' => $riwayat
                ->filter(fn($r) => $petaLulus->get(strtoupper($r->nilai_huruf_final), false))
                ->sum('sks_diakui'),
            'ipk' => $statusTerakhir?->ipk,
            'status_terakhir' => $statusTerakhir,
        ];
    }

    /**
     * Data Rekap Akademik: gabungan progres IPK per semester (untuk grafik)
     * dan progres SKS lulus vs total SKS wajib kelulusan kurikulum.
     */
    public function rekapAkademikData(Mahasiswa $mahasiswa): array
    {
        $riwayatPerSemester = $mahasiswa->riwayatStatus()
            ->with('tahunAkademik')
            ->orderBy('tahun_akademik_id')
            ->get();

        $totalSksWajib = $mahasiswa->kurikulum?->jumlah_sks_lulus ?? 0;
        $sksLulusSaatIni = $riwayatPerSemester->last()?->sks_total ?? 0;
        return [
            'riwayat_per_semester' => $riwayatPerSemester,
            'total_sks_wajib' => $totalSksWajib,
            'sks_lulus_saat_ini' => $sksLulusSaatIni,
            'persentase_progres' => $totalSksWajib > 0
                ? round(min($sksLulusSaatIni / $totalSksWajib, 1) * 100, 1)
                : 0,
            'distribusi_huruf' => $mahasiswa->transkrip()
                ->selectRaw('nilai_huruf_final, count(*) as jumlah')
                ->groupBy('nilai_huruf_final')
                ->pluck('jumlah', 'nilai_huruf_final'),
        ];
    }

    /** Peta huruf => is_lulus, diambil dari master ref_skala_nilai (bukan hardcode). */
    private function petaHurufLulus(): Collection
    {
        return RefSkalaNilai::query()->pluck('is_lulus', 'huruf')
            ->mapWithKeys(fn($v, $k) => [strtoupper($k) => (bool) $v]);
    }
}
