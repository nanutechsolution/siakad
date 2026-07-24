<?php

namespace App\Filament\Resources\LpmSurveyJawabanPihaks\Pages;

use App\Filament\Resources\LpmSurveyJawabanPihaks\LpmSurveyJawabanPihakResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLpmSurveyJawabanPihak extends ViewRecord
{
    protected static string $resource = LpmSurveyJawabanPihakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
