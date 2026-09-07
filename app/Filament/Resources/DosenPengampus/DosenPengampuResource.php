<?php

namespace App\Filament\Resources\DosenPengampus;

use App\Enums\NavigationGroup;
use App\Filament\Resources\DosenPengampus\Pages\CreateDosenPengampu;
use App\Filament\Resources\DosenPengampus\Pages\EditDosenPengampu;
use App\Filament\Resources\DosenPengampus\Pages\ListDosenPengampus;
use App\Filament\Resources\DosenPengampus\Schemas\DosenPengampuForm;
use App\Filament\Resources\DosenPengampus\Tables\DosenPengampusTable;
use App\Models\DosenPengampu;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DosenPengampuResource extends Resource
{
    protected static ?string $model = DosenPengampu::class;
    protected static string|BackedEnum|null $navigationIcon =
    Heroicon::OutlinedAcademicCap;
    protected static string|BackedEnum|null $activeNavigationIcon =
    Heroicon::AcademicCap;
    protected static ?string $navigationLabel = 'Dosen Pengampu';
    protected static ?string $modelLabel = 'Dosen Pengampu';
    protected static ?string $pluralModelLabel = 'Dosen Pengampu';
    protected static string|\UnitEnum|null $navigationGroup =  NavigationGroup::PERKULIAHAN->value;
    protected static ?int $navigationSort = 30;
    public static function form(Schema $schema): Schema
    {
        return DosenPengampuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DosenPengampusTable::configure($table);
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
            'index' => ListDosenPengampus::route('/'),
            'create' => CreateDosenPengampu::route('/create'),
            'edit' => EditDosenPengampu::route('/{record}/edit'),
        ];
    }
}
