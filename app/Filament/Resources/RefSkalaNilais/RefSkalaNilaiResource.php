<?php

namespace App\Filament\Resources\RefSkalaNilais;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RefSkalaNilais\Pages\ListRefSkalaNilais;
use App\Filament\Resources\RefSkalaNilais\Schemas\RefSkalaNilaiForm;
use App\Filament\Resources\RefSkalaNilais\Tables\RefSkalaNilaisTable;
use App\Models\RefSkalaNilai;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RefSkalaNilaiResource extends Resource
{
    protected static ?string $model = RefSkalaNilai::class;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::MASTER->value;

    protected static ?string $modelLabel = 'Skala Nilai';
    protected static ?string $slug = 'skala-nilai';

    protected static ?string $pluralModelLabel = 'Skala Nilai';

    public static function form(Schema $schema): Schema
    {
        return RefSkalaNilaiForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefSkalaNilaisTable::configure($table);
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
            'index' => ListRefSkalaNilais::route('/'),
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
