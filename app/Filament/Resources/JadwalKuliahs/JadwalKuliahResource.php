<?php

namespace App\Filament\Resources\JadwalKuliahs;

use App\Enums\NavigationGroup;
use App\Filament\Resources\JadwalKuliahs\Pages\CreateJadwalKuliah;
use App\Filament\Resources\JadwalKuliahs\Pages\EditJadwalKuliah;
use App\Filament\Resources\JadwalKuliahs\Pages\ListJadwalKuliahs;
use App\Filament\Resources\JadwalKuliahs\Schemas\JadwalKuliahForm;
use App\Filament\Resources\JadwalKuliahs\Tables\JadwalKuliahsTable;
use App\Models\JadwalKuliah;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class JadwalKuliahResource extends Resource
{
    protected static ?string $model = JadwalKuliah::class;
    protected static ?string $slug = 'perkuliahan/jadwal-kuliah';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::PERKULIAHAN->value;
    protected static ?string $modelLabel = 'Jadwal Kuliah';
    protected static ?string $pluralModelLabel = 'Jadwal Kuliah';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?int $navigationSort = 2;
    public static function form(Schema $schema): Schema
    {
        return JadwalKuliahForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JadwalKuliahsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // KomponenNilaiRelationManager::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => ListJadwalKuliahs::route('/'),
            'create' => CreateJadwalKuliah::route('/create'),
            'edit' => EditJadwalKuliah::route('/{record}/edit'),
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
