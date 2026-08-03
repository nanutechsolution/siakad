<?php

namespace App\Filament\Resources\KonfigurasiPembimbingAkademiks;

use App\Enums\NavigationGroup;
use App\Filament\Resources\KonfigurasiPembimbingAkademiks\Pages\ListKonfigurasiPembimbingAkademiks;
use App\Filament\Resources\KonfigurasiPembimbingAkademiks\Schemas\KonfigurasiPembimbingAkademikForm;
use App\Filament\Resources\KonfigurasiPembimbingAkademiks\Tables\KonfigurasiPembimbingAkademiksTable;
use App\Models\KonfigurasiPembimbingAkademik;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class KonfigurasiPembimbingAkademikResource extends Resource
{
    protected static ?string $model = KonfigurasiPembimbingAkademik::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::AKADEMIK->value;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationLabel = 'Konfigurasi Pembimbing';
    protected static ?string $modelLabel = 'Konfigurasi Pembimbing Akademik';
    protected static ?string $pluralModelLabel = 'Konfigurasi Pembimbing Akademik';
    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function getTitle(): string
    {
        return 'Konfigurasi Pembimbing Akademik';
    }

    public static function form(Schema $schema): Schema
    {
        return KonfigurasiPembimbingAkademikForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KonfigurasiPembimbingAkademiksTable::configure($table);
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
            'index' => ListKonfigurasiPembimbingAkademiks::route('/'),
        ];
    }
}
