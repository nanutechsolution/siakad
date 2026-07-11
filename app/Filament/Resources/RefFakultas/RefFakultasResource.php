<?php

namespace App\Filament\Resources\RefFakultas;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RefFakultas\Pages\CreateRefFakultas;
use App\Filament\Resources\RefFakultas\Pages\EditRefFakultas;
use App\Filament\Resources\RefFakultas\Pages\ListRefFakultas;
use App\Filament\Resources\RefFakultas\Schemas\RefFakultasForm;
use App\Filament\Resources\RefFakultas\Tables\RefFakultasTable;
use App\Models\RefFakultas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RefFakultasResource extends Resource
{
    protected static ?string $model = RefFakultas::class;

    protected static ?string $modelLabel = 'Fakultas';
    protected static ?string $pluralModelLabel = 'Fakultas';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::MASTER->value;
    }

    protected static ?string $recordTitleAttribute = 'nama_fakultas';

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_fakultas', 'nama_fakultas'];
    }
    public static function form(Schema $schema): Schema
    {
        return RefFakultasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefFakultasTable::configure($table);
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
            'index' => ListRefFakultas::route('/'),
            'create' => CreateRefFakultas::route('/create'),
            'edit' => EditRefFakultas::route('/{record}/edit'),
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
