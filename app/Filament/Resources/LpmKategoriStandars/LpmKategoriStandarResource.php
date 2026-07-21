<?php

namespace App\Filament\Resources\LpmKategoriStandars;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmKategoriStandars\Pages\ListLpmKategoriStandars;
use App\Filament\Resources\LpmKategoriStandars\Schemas\LpmKategoriStandarForm;
use App\Filament\Resources\LpmKategoriStandars\Tables\LpmKategoriStandarsTable;
use App\Models\LpmKategoriStandar;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LpmKategoriStandarResource extends Resource
{
    protected static ?string $model = LpmKategoriStandar::class;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    protected static ?string $navigationLabel = 'Kategori Standar';
    protected static ?string $modelLabel = 'Kategori Standar';
    public static function form(Schema $schema): Schema
    {
        return LpmKategoriStandarForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmKategoriStandarsTable::configure($table);
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
            'index' => ListLpmKategoriStandars::route('/'),
        ];
    }
}
