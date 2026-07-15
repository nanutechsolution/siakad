<?php

namespace App\Helpers;

use App\Models\RefTahunAkademik;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SistemHelper
{
    /**
     * Ambil ID Tahun Akademik yang sedang aktif (Flag is_active = true)
     * Disimpan di Cache selama 60 menit agar hemat query database.
     */
    public static function idTahunAktif()
    {
        return Cache::remember('ta_aktif_id', 3600, function () {
            $ta = RefTahunAkademik::where('is_active', true)->first();
            return $ta ? $ta->id : null;
        });
    }

    /**
     * Ambil Nama Tahun Akademik Aktif (Misal: 2025/2026 Ganjil)
     * Method ini ditambahkan untuk memperbaiki error Intelephense P1013.
     */
    public static function namaTahunAktif()
    {
        $ta = self::getTahunAktif();
        return $ta ? $ta->nama_tahun : 'Tidak Ada Periode Aktif';
    }


    /**
     * Logika Penentuan Dashboard Berdasarkan Prioritas Peran (Multi-role)
     * Digunakan untuk mengarahkan User setelah login atau tombol 'Overview'.
     * PRIORITAS: Admin/Staff > Dosen > Mahasiswa
     */
    public static function getDashboardRoute()
    {
        $user = Auth::user();
        if (!$user) return 'login';

        if ($user->hasRole('camaba')) {
            return 'camaba.dashboard';
        }
        // 1. Prioritas Tertinggi: Peran Administratif (Bisa mengelola sistem/keuangan)
        if ($user->hasAnyRole(['superadmin', 'admin', 'staf_prodi', 'bara', 'bauk', 'lpm', 'lppm'])) {
            return 'admin.dashboard';
        }

        // 2. Prioritas Kedua: Peran Akademik (Dosen)
        if ($user->hasRole('dosen')) {
            return 'dosen.dashboard';
        }
        // 3. Prioritas Terakhir: Mahasiswa
        if ($user->hasRole('mahasiswa')) {
            return 'mhs.dashboard';
        }

        return 'dashboard';
    }


    /**
     * Ambil Object Tahun Akademik Aktif secara lengkap
     */
    public static function getTahunAktif()
    {
        return Cache::remember('ta_aktif_obj', 3600, function () {
            return RefTahunAkademik::where('is_active', true)->first();
        });
    }

    /**
     * Cek apakah Masa KRS sedang dibuka berdasarkan tanggal
     */
    public static function isMasaKrsOpen(): bool
    {
        $ta = self::getTahunAktif();
        if (!$ta || !$ta->tgl_mulai_krs || !$ta->tgl_selesai_krs) return false;
        $now = now()->startOfDay();

        if (!$ta->buka_krs) return false;

        return $now->between($ta->tgl_mulai_krs, $ta->tgl_selesai_krs);
    }


    public static function semesterMahasiswa($mahasiswa): int
    {
        // validasi mahasiswa
        if (!$mahasiswa || !$mahasiswa->angkatan_id) {
            return 1;
        }

        $ta = self::getTahunAktif();
        if (!$ta || !$ta->kode_tahun) {
            return 1;
        }

        // Ambil tahun awal: 2026/2027 → 2026
        $tahunAktif = (int) substr($ta->kode_tahun, 0, 4);
        $tahunMasuk = (int) $mahasiswa->angkatan_id;

        // Semester pendek TIDAK menaikkan semester
        $semesterTA = $ta->semester == 3 ? 2 : $ta->semester;

        return max(1, (($tahunAktif - $tahunMasuk) * 2) + $semesterTA);
    }



    /**
     * Dapatkan path logo kop surat untuk PDF
     */
    public static function getKopLogoPath()
    {
        // Ganti dengan path logo yang sesuai di sistem Anda
        return public_path('logo.png');
    }


    /**
     * CORE LOGIC: Fungsi Pelacak ID Prodi berdasarkan Skema Database & Konteks
     * Memisahkan pelacakan untuk menghindari kebocoran data antar peran (Multi-Tenancy).
     * 
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string $konteks ('staf' atau 'dosen')
     * @return array
     */
    public static function resolveUserProdiIds($user, $konteks = 'staf'): array
    {
        if (!$user || !$user->person_id) return [];

        $prodi_ids = [];

        // 1. KONTEKS STAF/ADMIN: Hanya cek tabel jabatan struktural
        // Digunakan untuk modul manajerial seperti Kurikulum, Master MK, dll.
        if ($konteks === 'staf') {
            $jabatans = DB::table('trx_person_jabatan')
                ->where('person_id', $user->person_id)
                ->whereNotNull('prodi_id')
                ->where(function ($q) {
                    $q->whereNull('tanggal_selesai')
                        ->orWhere('tanggal_selesai', '>=', now()->toDateString());
                })
                ->get();

            foreach ($jabatans as $jabatan) {
                $prodi_ids[] = $jabatan->prodi_id;
            }

            return array_values(array_unique(array_filter($prodi_ids)));
        }

        // 2. KONTEKS DOSEN: Hanya cek tabel homebase dosen
        // Digunakan untuk modul akademik dosen seperti Input Nilai, Jadwal Ajar, dll.
        if ($konteks === 'dosen') {
            $dosen = DB::table('trx_dosen')
                ->where('person_id', $user->person_id)
                ->whereNull('deleted_at')
                ->first();

            if ($dosen && $dosen->prodi_id) {
                $prodi_ids[] = $dosen->prodi_id;
            }

            return array_values(array_unique(array_filter($prodi_ids)));
        }

        // Default jika konteks tidak dikenali
        return [];
    }


    /**
     * Helper untuk mengambil data Pejabat secara dinamis dari HR Module (SSOT)
     * Digunakan untuk keperluan Tanda Tangan, Cetak Dokumen, dan Otorisasi.
     * * @param string $kodeJabatan (Contoh: 'DEKAN', 'KAPRODI', 'REKTOR')
     * @param int|null $prodiId (Opsional: Diperlukan untuk jabatan spesifik prodi seperti KAPRODI)
     * @return object|null
     */
    public static function getPejabat($kodeJabatan, $prodiId = null)
    {
        $today = now()->format('Y-m-d');

        $query = DB::table('ref_person as p')
            ->join('trx_person_jabatan as pj', 'p.id', '=', 'pj.person_id')
            ->join('ref_jabatan as j', 'pj.jabatan_id', '=', 'j.id')
            ->leftJoin('trx_dosen as d', 'd.person_id', '=', 'p.id')
            ->where('j.kode_jabatan', $kodeJabatan)
            ->where('pj.tanggal_mulai', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('pj.tanggal_selesai')
                    ->orWhere('pj.tanggal_selesai', '>=', $today);
            });

        if ($prodiId) {
            $query->where('pj.prodi_id', $prodiId);
        }

        // Tambahkan d.nuptk ke dalam select query
        $person = $query->select('p.nama_lengkap', 'p.id', 'd.nidn', 'd.nuptk', 'p.nik', 'j.nama_jabatan')->first();

        if (!$person) return null;

        // Ambil Gelar Akademik
        $gelars = DB::table('trx_person_gelar as tpg')
            ->join('ref_gelar as rg', 'tpg.gelar_id', '=', 'rg.id')
            ->where('tpg.person_id', $person->id)
            ->select('rg.kode', 'rg.posisi')
            ->orderBy('tpg.urutan', 'asc')
            ->get();

        $gelarDepan = $gelars->where('posisi', 'DEPAN')->pluck('kode')->implode(' ');
        $gelarBelakang = $gelars->where('posisi', 'BELAKANG')->pluck('kode')->implode(', ');

        return (object)[
            'nama' => trim(($gelarDepan ? $gelarDepan . ' ' : '') . $person->nama_lengkap . ($gelarBelakang ? ', ' . $gelarBelakang : '')),
            'identitas' => $person->nidn ? "NIDN. " . $person->nidn : ($person->nuptk ? "NUPTK. " . $person->nuptk : "NIK. " . $person->nik),
            'jabatan' => $person->nama_jabatan
        ];
    }
}
