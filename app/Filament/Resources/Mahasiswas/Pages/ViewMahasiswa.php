<?php

namespace App\Filament\Resources\Mahasiswas\Pages;

use App\Filament\Resources\Mahasiswas\MahasiswaResource;
use App\Filament\Resources\Mahasiswas\Pages\Concerns\HasMahasiswaMutasiProdiAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMahasiswa extends ViewRecord
{
    use HasMahasiswaMutasiProdiAction;

    protected static string $resource = MahasiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            $this->makeMutasiProdiAction(),
        ];
    }
}
