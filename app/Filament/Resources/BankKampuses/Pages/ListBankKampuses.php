<?php

namespace App\Filament\Resources\BankKampuses\Pages;

use App\Filament\Resources\BankKampuses\BankKampusResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBankKampuses extends ListRecords
{
    protected static string $resource = BankKampusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
