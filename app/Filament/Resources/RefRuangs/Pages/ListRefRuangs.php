<?php

namespace App\Filament\Resources\RefRuangs\Pages;

use App\Filament\Resources\RefRuangs\RefRuangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRefRuangs extends ListRecords
{
    protected static string $resource = RefRuangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
