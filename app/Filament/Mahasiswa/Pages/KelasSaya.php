<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Filament\Mahasiswa\Concerns\ResolvesMahasiswa;
use App\Models\MahasiswaKelas;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use UnitEnum;

class KelasSaya extends Page
{
    use ResolvesMahasiswa;
    protected string $view = 'filament.mahasiswa.pages.kelas-saya';
    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::PERKULIAHAN->value;
    protected static ?string $navigationLabel = 'Kelas Saya';
    protected static ?string $title = 'Kelas Saya';
    protected static ?int $navigationSort = 5;
    protected static ?string $slug = 'kelas-saya';
    public function mount(): void
    {
        $this->currentMahasiswa();
    }
    /**
     * Keanggotaan kelas yang sedang aktif (tanggal_keluar belum diisi).
     * Ditulis sebagai koleksi (bukan single record) karena secara skema
     * mahasiswa_kelas TIDAK unique per mahasiswa -- kalau ada anomali data
     * (mis. mahasiswa tercatat aktif di 2 kelas sekaligus), tampilkan
     * semuanya secara eksplisit alih-alih diam-diam mengambil salah satu.
     */
    #[Computed]
    public function keanggotaanAktif(): Collection
    {
        return MahasiswaKelas::query()
            ->with([
                'kelas.prodi',
                'kelas.program',
                'kelas.dosenWali' => fn($q) => $q->wherePivot('is_primary', true),
                'kelas.dosenWali.person',
            ])
            ->where('mahasiswa_id', $this->currentMahasiswa()->id)
            ->aktif()
            ->get();
    }

    /**
     * Nama-nama teman sekelas (tanpa data pribadi lain) untuk satu kelas
     * tertentu. Dipanggil per kelas dari view, di-cache per request supaya
     * tidak query berulang kalau view di-render lebih dari sekali.
     */
    public function temanSekelas(int $kelasId): Collection
    {
        return MahasiswaKelas::query()
            ->with('mahasiswa.person')
            ->where('kelas_id', $kelasId)
            ->where('mahasiswa_id', '!=', $this->currentMahasiswa()->id)
            ->aktif()
            ->get()
            ->pluck('mahasiswa')
            ->filter()
            ->sortBy(fn($m) => $m->person?->nama_lengkap ?? $m->nim)
            ->values();
    }
}
