<?php

namespace App\Filament\Resources\MasterMataKuliahs;

use App\Enums\NavigationGroup;
use App\Filament\Resources\MasterMataKuliahs\Pages\CreateMasterMataKuliah;
use App\Filament\Resources\MasterMataKuliahs\Pages\EditMasterMataKuliah;
use App\Filament\Resources\MasterMataKuliahs\Pages\ListMasterMataKuliahs;
use App\Filament\Resources\MasterMataKuliahs\Schemas\MasterMataKuliahForm;
use App\Filament\Resources\MasterMataKuliahs\Tables\MasterMataKuliahsTable;
use App\Models\MasterMataKuliah;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterMataKuliahResource extends Resource
{
    protected static ?string $model = MasterMataKuliah::class;
    protected static ?string $slug = 'master-akademik/mata-kuliah';
    protected static ?string $modelLabel = 'Mata Kuliah';
    protected static ?string $pluralModelLabel = 'Mata Kuliah';
    protected static ?string $recordTitleAttribute = 'nama_mk';
    protected static ?int $navigationSort = 3;
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::MASTER->value;
    }
    public static function form(Schema $schema): Schema
    {
        return MasterMataKuliahForm::configure($schema);
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }
    public static function table(Table $table): Table
    {
        return MasterMataKuliahsTable::configure($table);
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
            'index' => ListMasterMataKuliahs::route('/'),
            'create' => CreateMasterMataKuliah::route('/create'),
            'edit' => EditMasterMataKuliah::route('/{record}/edit'),
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
