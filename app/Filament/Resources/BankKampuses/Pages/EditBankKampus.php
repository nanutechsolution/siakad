<?php

namespace App\Filament\Resources\BankKampuses\Pages;

use App\Filament\Resources\BankKampuses\BankKampusResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBankKampus extends EditRecord
{
    protected static string $resource = BankKampusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
