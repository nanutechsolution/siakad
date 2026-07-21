<?php

namespace App\Filament\Resources\LpmUnitKerjas;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmUnitKerjas\Pages\CreateLpmUnitKerja;
use App\Filament\Resources\LpmUnitKerjas\Pages\EditLpmUnitKerja;
use App\Filament\Resources\LpmUnitKerjas\Pages\ListLpmUnitKerjas;
use App\Filament\Resources\LpmUnitKerjas\RelationManagers\PicsRelationManager;
use App\Filament\Resources\LpmUnitKerjas\Schemas\LpmUnitKerjaForm;
use App\Filament\Resources\LpmUnitKerjas\Tables\LpmUnitKerjasTable;
use App\Models\LpmUnitKerja;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class LpmUnitKerjaResource extends Resource
{
    protected static ?string $model = LpmUnitKerja::class;
    protected static ?string $navigationLabel = 'Organisasi Mutu';
    protected static ?string $modelLabel = 'Unit Kerja';
    protected static ?string $pluralModelLabel = 'Unit Kerja';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    public static function form(Schema $schema): Schema
    {
        return LpmUnitKerjaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmUnitKerjasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PicsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpmUnitKerjas::route('/'),
            'create' => CreateLpmUnitKerja::route('/create'),
            'edit' => EditLpmUnitKerja::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
