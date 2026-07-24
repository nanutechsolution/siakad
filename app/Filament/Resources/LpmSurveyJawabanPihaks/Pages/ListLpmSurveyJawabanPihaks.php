<?php

namespace App\Filament\Resources\LpmSurveyJawabanPihaks\Pages;

use App\Filament\Resources\LpmSurveyJawabanPihaks\LpmSurveyJawabanPihakResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLpmSurveyJawabanPihaks extends ListRecords
{
    protected static string $resource = LpmSurveyJawabanPihakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
