<?php

namespace App\Filament\Resources\LpmSurveyJawabanPihaks\Pages;

use App\Filament\Resources\LpmSurveyJawabanPihaks\LpmSurveyJawabanPihakResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLpmSurveyJawabanPihak extends EditRecord
{
    protected static string $resource = LpmSurveyJawabanPihakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
