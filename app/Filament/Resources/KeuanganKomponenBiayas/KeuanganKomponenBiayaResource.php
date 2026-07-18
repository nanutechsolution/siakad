<?php

namespace App\Filament\Resources\KeuanganKomponenBiayas;

use App\Enums\NavigationGroup;
use App\Filament\Resources\KeuanganKomponenBiayas\Pages\ListKeuanganKomponenBiayas;
use App\Filament\Resources\KeuanganKomponenBiayas\Schemas\KeuanganKomponenBiayaForm;
use App\Filament\Resources\KeuanganKomponenBiayas\Tables\KeuanganKomponenBiayasTable;
use App\Models\KeuanganKomponenBiaya;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class KeuanganKomponenBiayaResource extends Resource
{
    protected static ?string $model = KeuanganKomponenBiaya::class;
    protected static ?string $slug = 'keuangan/komponen-biaya';
    protected static ?string $modelLabel = 'Komponen Biaya';
    protected static ?string $pluralModelLabel = 'Komponen Biaya';
    protected static ?string $recordTitleAttribute = 'nama';
    protected static ?int $navigationSort = 1;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;
    protected static bool $canCreateAnother = false;
    public static function form(Schema $schema): Schema
    {
        return KeuanganKomponenBiayaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KeuanganKomponenBiayasTable::configure($table);
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
            'index' => ListKeuanganKomponenBiayas::route('/'),
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
