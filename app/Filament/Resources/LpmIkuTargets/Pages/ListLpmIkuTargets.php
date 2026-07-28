<?php

namespace App\Filament\Resources\LpmIkuTargets\Pages;

use App\Filament\Resources\LpmIkuTargets\LpmIkuTargetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmIkuTargets extends ListRecords
{
    protected static string $resource = LpmIkuTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
