<?php

namespace App\Filament\Resources\MasterMataKuliahs\Pages;

use App\Filament\Resources\MasterMataKuliahs\MasterMataKuliahResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterMataKuliah extends EditRecord
{
    protected static string $resource = MasterMataKuliahResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
