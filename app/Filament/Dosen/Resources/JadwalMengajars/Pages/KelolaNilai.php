<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Pages;

use App\Filament\Dosen\Resources\JadwalMengajars\JadwalMengajarResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class KelolaNilai extends Page
{
    use InteractsWithRecord;

    protected static string $resource = JadwalMengajarResource::class;

    protected string $view = 'filament.dosen.resources.jadwal-mengajars.pages.kelola-nilai';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
