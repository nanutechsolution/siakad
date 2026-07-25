<?php

namespace App\Filament\Resources\LpmBenchmarks\Pages;

use App\Filament\Resources\LpmBenchmarks\LpmBenchmarkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmBenchmark extends EditRecord
{
    protected static string $resource = LpmBenchmarkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
