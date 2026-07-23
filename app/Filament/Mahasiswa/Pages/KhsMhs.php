<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Models\Mahasiswa as MahasiswaModel;
use App\Enums\MahasiswaNavigationGroup;
use App\Services\Mahasiswa\NilaiAkademikService;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

class KhsMhs extends Page
{
    protected string $view = 'filament.mahasiswa.pages.khs-mhs';

    protected static ?string $navigationLabel = 'KHS';
    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::NILAI->value;
    protected static ?string $title = 'Kartu Hasil Studi';
    protected static ?int $navigationSort = 2;

    public ?int $tahunAkademikId = null;

    public MahasiswaModel $mahasiswa;

    public Collection $daftarTahunAkademik;

    public array $khs = [];
    public function mount(NilaiAkademikService $service): void
    {
        $this->mahasiswa = $service->mahasiswaLogin();
        $this->daftarTahunAkademik = $service->daftarTahunAkademikMahasiswa($this->mahasiswa);

        // Default: tahun akademik paling baru yang punya data.
        $this->tahunAkademikId = $this->daftarTahunAkademik->first()?->id;

        $this->loadKhs($service);
    }

    /** Dipanggil ulang otomatis oleh Livewire tiap kali filter diubah. */
    public function updatedTahunAkademikId(NilaiAkademikService $service): void
    {
        $this->loadKhs($service);
    }

    protected function loadKhs(NilaiAkademikService $service): void
    {
        $this->khs = $this->tahunAkademikId
            ? $service->khsData($this->mahasiswa, $this->tahunAkademikId)
            : ['ringkasan' => null, 'mata_kuliah' => collect(), 'jumlah_mk' => 0];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->url(fn() => route('mahasiswa.khs.pdf', ['tahunAkademikId' => $this->tahunAkademikId]))
                ->openUrlInNewTab()
                ->disabled(fn() => blank($this->tahunAkademikId)),
        ];
    }
}
