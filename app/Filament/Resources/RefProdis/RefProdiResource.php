<?php

namespace App\Filament\Resources\RefProdis;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RefProdis\Pages\CreateRefProdi;
use App\Filament\Resources\RefProdis\Pages\EditRefProdi;
use App\Filament\Resources\RefProdis\Pages\ListRefProdis;
use App\Filament\Resources\RefProdis\Schemas\RefProdiForm;
use App\Filament\Resources\RefProdis\Tables\RefProdisTable;
use App\Models\RefProdi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RefProdiResource extends Resource
{
    protected static ?string $model = RefProdi::class;
    protected static ?string $modelLabel = 'Program Studi';
    protected static ?string $pluralModelLabel = 'Program Studi';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::MASTER->value;
    }

    protected static ?string $recordTitleAttribute = 'nama_prodi';

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_prodi_internal', 'kode_prodi_dikti', 'nama_prodi'];
    }

    public static function form(Schema $schema): Schema
    {
        return RefProdiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefProdisTable::configure($table);
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
            'index' => ListRefProdis::route('/'),
            'create' => CreateRefProdi::route('/create'),
            'edit' => EditRefProdi::route('/{record}/edit'),
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
