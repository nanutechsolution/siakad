<?php

namespace App\Filament\Clusters\ManajemenKelas\Resources\Manajemenkelas\Pages;

use App\Filament\Clusters\ManajemenKelas\Resources\Manajemenkelas\ManajemenkelasResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditManajemenkelas extends EditRecord
{
    protected static string $resource = ManajemenkelasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    
}
