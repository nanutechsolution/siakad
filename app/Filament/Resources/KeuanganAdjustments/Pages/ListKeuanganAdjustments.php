<?php

namespace App\Filament\Resources\KeuanganAdjustments\Pages;

use App\Filament\Resources\KeuanganAdjustments\KeuanganAdjustmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKeuanganAdjustments extends ListRecords
{
    protected static string $resource = KeuanganAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
