<?php

namespace App\Filament\Resources\LpmBenchmarkInstitusis\Pages;

use App\Filament\Resources\LpmBenchmarkInstitusis\LpmBenchmarkInstitusiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmBenchmarkInstitusi extends EditRecord
{
    protected static string $resource = LpmBenchmarkInstitusiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
