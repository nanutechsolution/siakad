<?php

namespace App\Filament\Resources\KurikulumMataKuliahs\Pages;

use App\Filament\Resources\KurikulumMataKuliahs\KurikulumMataKuliahResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKurikulumMataKuliah extends EditRecord
{
    protected static string $resource = KurikulumMataKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
