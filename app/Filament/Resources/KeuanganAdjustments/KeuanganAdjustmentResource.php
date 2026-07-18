<?php

namespace App\Filament\Resources\KeuanganAdjustments;

use App\Enums\NavigationGroup;
use App\Filament\Resources\KeuanganAdjustments\Pages\CreateKeuanganAdjustment;
use App\Filament\Resources\KeuanganAdjustments\Pages\EditKeuanganAdjustment;
use App\Filament\Resources\KeuanganAdjustments\Pages\ListKeuanganAdjustments;
use App\Filament\Resources\KeuanganAdjustments\Pages\ViewKeuanganAdjustment;
use App\Filament\Resources\KeuanganAdjustments\Schemas\KeuanganAdjustmentForm;
use App\Filament\Resources\KeuanganAdjustments\Schemas\KeuanganAdjustmentInfolist;
use App\Filament\Resources\KeuanganAdjustments\Tables\KeuanganAdjustmentsTable;
use App\Models\KeuanganAdjustment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class KeuanganAdjustmentResource extends Resource
{
    protected static ?string $model = KeuanganAdjustment::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;

    protected static ?string $modelLabel = 'Penyesuaian Keuangan';

    protected static ?string $pluralModelLabel = 'Penyesuaian Keuangan';

    protected static ?int $navigationSort = 8;
    public static function form(Schema $schema): Schema
    {
        return KeuanganAdjustmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KeuanganAdjustmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KeuanganAdjustmentsTable::configure($table);
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
            'index' => ListKeuanganAdjustments::route('/'),
            'create' => CreateKeuanganAdjustment::route('/create'),
            'view' => ViewKeuanganAdjustment::route('/{record}'),
            'edit' => EditKeuanganAdjustment::route('/{record}/edit'),
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
