<?php

namespace App\Filament\Resources\DosenDokumens\Pages;

use App\Filament\Resources\DosenDokumens\DosenDokumenResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDosenDokumen extends EditRecord
{
    protected static string $resource = DosenDokumenResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
