<?php

namespace App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Pages;

use App\Filament\Mahasiswa\Resources\TagihanNonRegulers\TagihanNonRegulerResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTagihanNonReguler extends EditRecord
{
    protected static string $resource = TagihanNonRegulerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
