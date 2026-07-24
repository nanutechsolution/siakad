<?php

namespace App\Filament\Resources\LpmSurveyJawabanPihaks;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmSurveyJawabanPihaks\Pages\CreateLpmSurveyJawabanPihak;
use App\Filament\Resources\LpmSurveyJawabanPihaks\Pages\EditLpmSurveyJawabanPihak;
use App\Filament\Resources\LpmSurveyJawabanPihaks\Pages\ListLpmSurveyJawabanPihaks;
use App\Filament\Resources\LpmSurveyJawabanPihaks\Pages\ViewLpmSurveyJawabanPihak;
use App\Filament\Resources\LpmSurveyJawabanPihaks\Schemas\LpmSurveyJawabanPihakForm;
use App\Filament\Resources\LpmSurveyJawabanPihaks\Schemas\LpmSurveyJawabanPihakInfolist;
use App\Filament\Resources\LpmSurveyJawabanPihaks\Tables\LpmSurveyJawabanPihaksTable;
use App\Models\LpmSurveyJawabanPihak;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LpmSurveyJawabanPihakResource extends Resource
{
    protected static ?string $model = LpmSurveyJawabanPihak::class;
    protected static ?string $navigationLabel = 'Entri Survey Dosen/Tendik/Alumni';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    protected static ?string $modelLabel = 'Jawaban Survey Pihak Lain';
    public static function form(Schema $schema): Schema
    {
        return LpmSurveyJawabanPihakForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LpmSurveyJawabanPihakInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmSurveyJawabanPihaksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpmSurveyJawabanPihaks::route('/'),
            'create' => CreateLpmSurveyJawabanPihak::route('/create'),
            'view' => ViewLpmSurveyJawabanPihak::route('/{record}'),
            'edit' => EditLpmSurveyJawabanPihak::route('/{record}/edit'),
        ];
    }
}
