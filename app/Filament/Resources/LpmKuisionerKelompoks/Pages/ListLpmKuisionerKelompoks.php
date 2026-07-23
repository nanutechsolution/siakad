<?php

namespace App\Filament\Resources\LpmKuisionerKelompoks\Pages;

use App\Filament\Resources\LpmKuisionerKelompoks\LpmKuisionerKelompokResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmKuisionerKelompoks extends ListRecords
{
    protected static string $resource = LpmKuisionerKelompokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
