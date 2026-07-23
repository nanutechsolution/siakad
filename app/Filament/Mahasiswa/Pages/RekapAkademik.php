<?php

namespace App\Filament\Mahasiswa\Pages;

use App\Enums\MahasiswaNavigationGroup;
use App\Services\Mahasiswa\NilaiAkademikService;
use Filament\Pages\Page;
use UnitEnum;

class RekapAkademik extends Page
{
    protected string $view = 'filament.mahasiswa.pages.rekap-akademik';
    protected static ?string $navigationLabel = 'Rekap Akademik';
    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::NILAI->value;
    protected static ?string $title = 'Rekap Akademik';
    protected static ?int $navigationSort = 4;
    public array $data = [];


    public function mount(NilaiAkademikService $service): void
    {
        $mahasiswa = $service->mahasiswaLogin();
        $this->data = $service->rekapAkademikData($mahasiswa);
    }
}
