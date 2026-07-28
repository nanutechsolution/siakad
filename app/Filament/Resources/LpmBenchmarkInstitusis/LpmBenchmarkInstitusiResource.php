<?php

namespace App\Filament\Resources\LpmBenchmarkInstitusis;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmBenchmarkInstitusis\Pages\CreateLpmBenchmarkInstitusi;
use App\Filament\Resources\LpmBenchmarkInstitusis\Pages\EditLpmBenchmarkInstitusi;
use App\Filament\Resources\LpmBenchmarkInstitusis\Pages\ListLpmBenchmarkInstitusis;
use App\Filament\Resources\LpmBenchmarkInstitusis\Schemas\LpmBenchmarkInstitusiForm;
use App\Filament\Resources\LpmBenchmarkInstitusis\Tables\LpmBenchmarkInstitusisTable;
use App\Models\LpmBenchmarkInstitusi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LpmBenchmarkInstitusiResource extends Resource
{
    protected static ?string $model = LpmBenchmarkInstitusi::class;
    protected static ?string $navigationLabel = 'Institusi Pembanding';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    protected static ?string $modelLabel = 'Institusi Pembanding';
    protected static ?int $navigationSort = 13;

    public static function form(Schema $schema): Schema
    {
        return LpmBenchmarkInstitusiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmBenchmarkInstitusisTable::configure($table);
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
            'index' => ListLpmBenchmarkInstitusis::route('/'),
            'create' => CreateLpmBenchmarkInstitusi::route('/create'),
            'edit' => EditLpmBenchmarkInstitusi::route('/{record}/edit'),
        ];
    }
}
