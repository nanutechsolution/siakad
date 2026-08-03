<?php

namespace App\Filament\Resources\KonfigurasiPembimbingAkademiks\Pages;

use App\Filament\Resources\KonfigurasiPembimbingAkademiks\KonfigurasiPembimbingAkademikResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKonfigurasiPembimbingAkademik extends EditRecord
{
    protected static string $resource = KonfigurasiPembimbingAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
