<?php

namespace App\Filament\Resources\KeuanganSkemaTarifs;

use App\Enums\NavigationGroup;
use App\Filament\Resources\KeuanganSkemaTarifs\Pages\CreateKeuanganSkemaTarif;
use App\Filament\Resources\KeuanganSkemaTarifs\Pages\EditKeuanganSkemaTarif;
use App\Filament\Resources\KeuanganSkemaTarifs\Pages\ListKeuanganSkemaTarifs;
use App\Filament\Resources\KeuanganSkemaTarifs\RelationManagers\DetailTarifsRelationManager;
use App\Filament\Resources\KeuanganSkemaTarifs\Schemas\KeuanganSkemaTarifForm;
use App\Filament\Resources\KeuanganSkemaTarifs\Tables\KeuanganSkemaTarifsTable;
use App\Models\KeuanganSkemaTarif;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class KeuanganSkemaTarifResource extends Resource
{
    protected static ?string $model = KeuanganSkemaTarif::class;

    protected static ?string $slug = 'keuangan/skema-tarif';
    protected static ?string $modelLabel = 'Skema Tarif';

    protected static ?string $pluralModelLabel = 'Skema Tarif';

    protected static ?string $recordTitleAttribute = 'nama';

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;

    public static function form(Schema $schema): Schema
    {
        return KeuanganSkemaTarifForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KeuanganSkemaTarifsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            DetailTarifsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKeuanganSkemaTarifs::route('/'),
            'create' => CreateKeuanganSkemaTarif::route('/create'),
            'edit' => EditKeuanganSkemaTarif::route('/{record}/edit'),
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
