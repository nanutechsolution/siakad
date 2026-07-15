<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\NavigationGroup;
use App\Filament\Mahasiswa\Concerns\ResolvesMahasiswa;
use App\Models\JadwalKuliah;
use App\Models\RefTahunAkademik;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use UnitEnum;

class JadwalKuliahMhs extends Page
{
    use ResolvesMahasiswa;
    protected string $view = 'filament.mahasiswa.pages.jadwal-kuliah-mhs';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::PERKULIAHAN->value;
    protected static ?string $navigationLabel = 'Jadwal Kuliah';
    protected static ?string $title = 'Jadwal Kuliah';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'jadwal-kuliah';
    /**
     * Tahun akademik yang sedang dipilih di dropdown filter.
     * Livewire property -- otomatis persist antar request via query string
     * supaya link "jadwal semester lalu" bisa di-bookmark/di-share.
     */
    public ?int $tahunAkademikId = null;

    protected $queryString = ['tahunAkademikId'];

    public function mount(): void
    {
        // Pastikan user terverifikasi sebagai mahasiswa sebelum apa pun lain
        // dieksekusi -- kalau gagal, exception dari trait otomatis jadi 403.
        $this->currentMahasiswa();

        $this->tahunAkademikId ??= $this->tahunAkademikAktif()?->id
            ?? $this->daftarTahunAkademikMahasiswa()->first()?->id;
    }

    /**
     * Semua tahun akademik yang PERNAH mahasiswa ini punya KRS,
     * diurutkan terbaru dulu. Dipakai untuk mengisi pilihan dropdown
     * semester -- jangan tampilkan semua ref_tahun_akademik yang ada di
     * sistem, karena mahasiswa baru bisa jadi belum punya riwayat.
     */
    public function daftarTahunAkademikMahasiswa(): Collection
    {
        return RefTahunAkademik::query()
            ->whereIn('id', $this->currentMahasiswa()->krs()->pluck('tahun_akademik_id'))
            ->orderByDesc('tanggal_mulai')
            ->get();
    }

    /**
     * Jadwal kuliah mahasiswa untuk tahun akademik terpilih, dikelompokkan
     * per hari (Senin..Minggu) dan diurutkan per jam.
     *
     * @return Collection<string, Collection<int, JadwalKuliah>>
     */
    #[Computed]
    public function jadwalPerHari(): Collection
    {
        if (! $this->tahunAkademikId) {
            return collect();
        }

        $krs = $this->currentMahasiswa()
            ->krs()
            ->berlaku()
            ->where('tahun_akademik_id', $this->tahunAkademikId)
            ->first();

        if (! $krs) {
            return collect();
        }

        $jadwalKuliahIds = $krs->detail()
            ->aktif()
            ->whereNotNull('jadwal_kuliah_id')
            ->pluck('jadwal_kuliah_id');

        if ($jadwalKuliahIds->isEmpty()) {
            return collect();
        }

        return JadwalKuliah::query()
            ->with(['mataKuliah', 'ruang', 'kelas', 'dosenPengampus.dosen.person'])
            ->whereIn('id', $jadwalKuliahIds)
            ->get()
            ->groupBy(fn(JadwalKuliah $jadwal) => $jadwal->hari ?? 'Belum Ditentukan')
            ->sortBy(
                fn(Collection $items, string $hari) => JadwalKuliah::URUTAN_HARI[$hari] ?? 99
            )
            ->map(
                fn(Collection $items) => $items->sortBy('jam_mulai')->values()
            );
    }

    /**
     * Mata kuliah yang diambil tapi tidak punya jadwal_kuliah_id
     * (mis. skripsi/KKN/kelas mandiri) -- ditampilkan terpisah supaya
     * mahasiswa tidak bingung kenapa SKS totalnya tidak cocok dengan
     * jumlah baris di jadwal mingguan.
     */
    #[Computed]
    public function mataKuliahTanpaJadwal(): Collection
    {
        if (! $this->tahunAkademikId) {
            return collect();
        }

        $krs = $this->currentMahasiswa()
            ->krs()
            ->berlaku()
            ->where('tahun_akademik_id', $this->tahunAkademikId)
            ->first();

        if (! $krs) {
            return collect();
        }

        return $krs->detail()
            ->aktif()
            ->whereNull('jadwal_kuliah_id')
            ->get();
    }

    public function getHeading(): string
    {
        return 'Jadwal Kuliah';
    }
}
