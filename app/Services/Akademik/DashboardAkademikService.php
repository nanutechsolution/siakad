<?php

declare(strict_types=1);

namespace App\Services\Akademik;

use App\Enums\KrsStatusEnum;
use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikStatus;
use App\Enums\StatusKuliah;
use App\Filament\Resources\RefTahunAkademiks\RefTahunAkademikResource;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\MasterKurikulum;
use App\Models\PembimbingAkademik;
use App\Models\RefTahunAkademik;
use App\Models\RiwayatStatusMahasiswa;
use App\Models\TrxDosen;
use Illuminate\Database\Eloquent\Builder;

/**
 * SATU-SATUNYA tempat query dasar dibangun untuk widget Dashboard Akademik.
 *
 * Setiap query SELALU memakai Model::visibleTo($user) sebagai lapis paling
 * dasar (pola yang sama dengan ScopedMonitoringQueries pada Monitoring KRS),
 * sehingga Admin Prodi otomatis hanya melihat data Prodi miliknya tanpa
 * perlu memilih filter — sedangkan role global tetap melihat datanya sendiri.
 */
final class DashboardAkademikService
{
    public function tahunAktif(): ?RefTahunAkademik
    {
        return RefTahunAkademik::query()
            ->where('is_active', true)
            ->first();
    }

    /**
     * Baseline scope: selalu mulai dari Model::visibleTo($user).
     * Tanpa user login hasilnya kosong (bukan "semua data") supaya tidak ada
     * panggilan dari konteks non-auth (queue/konsole) yang membocorkan data.
     */
    private function scoped(string $modelClass): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return $modelClass::query()->whereRaw('1 = 0');
        }

        return $modelClass::query()->visibleTo($user);
    }

    public function mahasiswaAktifQuery(): Builder
    {
        $ta = $this->tahunAktif();
        $query = $this->scoped(Mahasiswa::class)->whereNull('deleted_at');

        // Riwayat status kuliah adalah sumber status "aktif" — tetapi datanya
        // sering belum lengkap untuk TA berjalan (kemungkinan masih di-import).
        // Kalau TA aktif belum punya baris sama sekali, jangan sembunyikan
        // seluruh mahasiswa (angka 0 menyesatkan): pakai semua mahasiswa
        // dan tandai konteksnya sebagai "tanpa verifikasi status" di overview().
        if (! $ta || ! RiwayatStatusMahasiswa::query()
            ->where('tahun_akademik_id', $ta->id)
            ->where('status_kuliah', StatusKuliah::AKTIF->value)
            ->exists()
        ) {
            return $query;
        }

        return $query->whereHas(
            'riwayatStatus',
            fn(Builder $status) => $status
                ->where('tahun_akademik_id', $ta->id)
                ->where('status_kuliah', StatusKuliah::AKTIF->value),
        );
    }

    /**
     * true bila status kuliah TA aktif belum diisi sama sekali — untuk
     * memberi konteks pada deskripsi stat, bukan membiarkan pengguna
    * mengira seluruh mahasiswa berjumlah nol.
     */
    public function statusBelumTerkirim(): bool
    {
        $ta = $this->tahunAktif();

        return $ta === null || ! RiwayatStatusMahasiswa::query()
            ->where('tahun_akademik_id', $ta->id)
            ->exists();
    }

    public function krsQuery(): Builder
    {
        $ta = $this->tahunAktif();

        return $this->scoped(Krs::class)
            ->when($ta, fn(Builder $q) => $q->where('tahun_akademik_id', $ta->id));
    }

    public function kelasQuery(): Builder
    {
        return $this->scoped(Kelas::class);
    }

    /**
     * Label konteks scope yang ditampilkan di dashboard, supaya user paham
     * apakah angka di layar mencakup satu prodi atau seluruh institusi.
     */
    public function contextLabel(): string
    {
        $user = auth()->user();

        if ($user && ($user->isAdminProdi() || $user->isKaprodi())) {
            $ids = $user->accessibleProdi();

            if ($ids === []) {
                return 'Tidak ada Prodi dalam hak akses Anda';
            }

            return count($ids) === 1 ? 'Program Studi Anda' : 'Program Studi dalam hak akses Anda';
        }

        return 'Sesuai hak akses akun Anda';
    }

    public function overview(): array
    {
        $mahasiswa = $this->mahasiswaAktifQuery();
        $krs = $this->krsQuery();

        return [
            'mahasiswa' => (clone $mahasiswa)->count(),
            'belum_kelas' => (clone $mahasiswa)->whereDoesntHave('kelasAktif')->count(),
            'dosen' => $this->scoped(TrxDosen::class)
                ->where('is_active', true)
                ->count(),
            'kurikulum' => $this->scoped(MasterKurikulum::class)->count(),
            'menunggu' => (clone $krs)->where('status_krs', KrsStatusEnum::DIAJUKAN)->count(),
            'disetujui' => (clone $krs)->where('status_krs', KrsStatusEnum::DISETUJUI)->count(),
            'ditolak' => (clone $krs)->where('status_krs', KrsStatusEnum::DITOLAK)->count(),
            'tahun' => $this->tahunAktif()?->nama_tahun ?? 'Belum ada TA aktif',
            'status_belum_terkirim' => $this->statusBelumTerkirim(),
        ];
    }

    /**
     * Peringatan prioritas untuk admin, diurutkan dari yang paling mendesak.
     * Setiap item membawa link menuju halaman tindak lanjutnya.
     */
    public function alerts(): array
    {
        if (! $this->tahunAktif()) {
            return [
                $this->alert('danger', 'heroicon-o-calendar-days', 'Tahun akademik aktif belum ditentukan', 1,
                    'Tetapkan periode aktif agar monitoring akademik dapat berjalan.',
                    RefTahunAkademikResource::getUrl('index')),
            ];
        }

        $mahasiswa = $this->mahasiswaAktifQuery();
        $krs = $this->krsQuery();
        $kelas = $this->kelasQuery();

        $waliClassIds = PembimbingAkademik::query()
            ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->whereNotNull('kelas_id')
            ->pluck('kelas_id');

        $tanpaWali = (clone $kelas)
            ->whereHas('mahasiswaKelasAktif')
            ->whereNotIn('id', $waliClassIds)
            ->count();

        $overCapacity = (clone $kelas)
            ->whereNotNull('kapasitas')
            ->withCount(['mahasiswaKelasAktif as isi'])
            ->get()
            ->filter(fn(Kelas $k) => $k->isi > $k->kapasitas)
            ->count();

        $sudahMengisi = (clone $krs)
            ->whereIn('status_krs', KrsStatusEnum::sudahMengisiValues())
            ->pluck('mahasiswa_id');

        return [
            $this->alert('danger', 'heroicon-o-clock', 'KRS menunggu persetujuan',
                (clone $krs)->where('status_krs', KrsStatusEnum::DIAJUKAN)->count(),
                'Periksa dan proses pengajuan KRS mahasiswa.',
                \App\Filament\Resources\Krs\KrsResource::getUrl('index')),

            $this->alert('danger', 'heroicon-o-document-minus', 'Mahasiswa belum mengisi KRS',
                (clone $mahasiswa)->whereNotIn('id', $sudahMengisi)->count(),
                'Mahasiswa aktif pada periode ini belum mengajukan KRS.',
                \App\Filament\Pages\MonitoringKrs::getUrl()),

            $this->alert('danger', 'heroicon-o-arrow-trending-up', 'Kelas melebihi kapasitas',
                $overCapacity,
                'Periksa distribusi mahasiswa dan kapasitas kelas.',
                \App\Filament\Clusters\ManajemenKelas\Pages\MonitoringKelasPage::getUrl()),

            $this->alert('warning', 'heroicon-o-x-circle', 'KRS ditolak',
                (clone $krs)->where('status_krs', KrsStatusEnum::DITOLAK)->count(),
                'Perlu ditindaklanjuti atau diperbaiki oleh mahasiswa.',
                \App\Filament\Resources\Krs\KrsResource::getUrl('index')),

            $this->alert('warning', 'heroicon-o-user-minus', 'Kelas tanpa Dosen Wali',
                $tanpaWali,
                'Kelas berisi mahasiswa tetapi belum punya Dosen Wali aktif.',
                \App\Filament\Clusters\ManajemenKelas\Pages\MonitoringKelasPage::getUrl()),

            $this->alert('warning', 'heroicon-o-user-group', 'Mahasiswa belum punya kelas',
                (clone $mahasiswa)->whereDoesntHave('kelasAktif')->count(),
                'Tempatkan mahasiswa ke kelas aktifnya.',
                \App\Filament\Clusters\ManajemenKelas\Pages\PenempatanMahasiswaPage::getUrl()),
        ];
    }

    private function alert(
        string $severity,
        string $icon,
        string $label,
        int $total,
        string $description,
        string $url,
    ): array {
        return [
            'severity' => $severity,
            'icon' => $icon,
            'label' => $label,
            'total' => $total,
            'description' => $description,
            'url' => $url,
        ];
    }
}
