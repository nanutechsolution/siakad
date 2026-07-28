<?php

namespace App\Filament\Resources\LpmAkreditasis;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmAkreditasis\Pages\CreateLpmAkreditasi;
use App\Filament\Resources\LpmAkreditasis\Pages\EditLpmAkreditasi;
use App\Filament\Resources\LpmAkreditasis\Pages\ListLpmAkreditasis;
use App\Filament\Resources\LpmAkreditasis\RelationManagers\KriteriasRelationManager;
use App\Filament\Resources\LpmAkreditasis\Schemas\LpmAkreditasiForm;
use App\Filament\Resources\LpmAkreditasis\Tables\LpmAkreditasisTable;
use App\Models\LpmAkreditasi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LpmAkreditasiResource extends Resource
{
    protected static ?string $model = LpmAkreditasi::class;

    protected static ?string $navigationLabel = 'Akreditasi';
    
    protected static ?int $navigationSort = 12;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    
    protected static ?string $modelLabel = 'Proses Akreditasi';
    public static function form(Schema $schema): Schema
    {
        return LpmAkreditasiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmAkreditasisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            KriteriasRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpmAkreditasis::route('/'),
            'create' => CreateLpmAkreditasi::route('/create'),
            'edit' => EditLpmAkreditasi::route('/{record}/edit'),
        ];
    }
}
