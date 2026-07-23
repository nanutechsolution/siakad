<?php

namespace App\Filament\Resources\LpmKuisionerKelompoks;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmKuisionerKelompoks\Pages\CreateLpmKuisionerKelompok;
use App\Filament\Resources\LpmKuisionerKelompoks\Pages\EditLpmKuisionerKelompok;
use App\Filament\Resources\LpmKuisionerKelompoks\Pages\ListLpmKuisionerKelompoks;
use App\Filament\Resources\LpmKuisionerKelompoks\RelationManagers\JawabanPihaksRelationManager;
use App\Filament\Resources\LpmKuisionerKelompoks\RelationManagers\PertanyaansRelationManager;
use App\Filament\Resources\LpmKuisionerKelompoks\Schemas\LpmKuisionerKelompokForm;
use App\Filament\Resources\LpmKuisionerKelompoks\Tables\LpmKuisionerKelompoksTable;
use App\Models\LpmKuisionerKelompok;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LpmKuisionerKelompokResource extends Resource
{
    protected static ?string $model = LpmKuisionerKelompok::class;
    protected static ?string $navigationLabel = 'Instrumen Kuisioner';
    protected static ?string $modelLabel = 'Kelompok Kuisioner';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    public static function form(Schema $schema): Schema
    {
        return LpmKuisionerKelompokForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmKuisionerKelompoksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PertanyaansRelationManager::class,
            JawabanPihaksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpmKuisionerKelompoks::route('/'),
            'create' => CreateLpmKuisionerKelompok::route('/create'),
            'edit' => EditLpmKuisionerKelompok::route('/{record}/edit'),
        ];
    }
}
