<?php

namespace App\Filament\Resources\LpmAkreditasiKriterias;

use App\Filament\Resources\LpmAkreditasiKriterias\Pages\EditLpmAkreditasiKriteria;
use App\Filament\Resources\LpmAkreditasiKriterias\Pages\ListLpmAkreditasiKriterias;
use App\Filament\Resources\LpmAkreditasiKriterias\RelationManagers\ElemensRelationManager;
use App\Filament\Resources\LpmAkreditasiKriterias\Schemas\LpmAkreditasiKriteriaForm;
use App\Filament\Resources\LpmAkreditasiKriterias\Tables\LpmAkreditasiKriteriasTable;
use App\Models\LpmAkreditasiKriteria;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

class LpmAkreditasiKriteriaResource extends Resource
{
    protected static ?string $model = LpmAkreditasiKriteria::class;
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Schema $schema): Schema
    {
        return LpmAkreditasiKriteriaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmAkreditasiKriteriasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ElemensRelationManager::class,
        ];
    }
    #[Override]
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpmAkreditasiKriterias::route('/'),
            'edit' => EditLpmAkreditasiKriteria::route('/{record}/edit'),
        ];
    }
}
