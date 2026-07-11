<?php

namespace App\Filament\Resources\RefTahunAkademiks\Pages;

use App\Filament\Resources\RefTahunAkademiks\RefTahunAkademikResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRefTahunAkademik extends EditRecord
{
    protected static string $resource = RefTahunAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
