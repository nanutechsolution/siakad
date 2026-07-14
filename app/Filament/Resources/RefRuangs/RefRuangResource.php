<?php

namespace App\Filament\Resources\RefRuangs;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RefRuangs\Pages\ListRefRuangs;
use App\Filament\Resources\RefRuangs\Schemas\RefRuangForm;
use App\Filament\Resources\RefRuangs\Tables\RefRuangsTable;
use App\Models\RefRuang;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RefRuangResource extends Resource
{
    protected static ?string $model = RefRuang::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::MASTER->value;

    protected static ?string $modelLabel = 'Data Ruangan';
    protected static ?string $slug = "ruangan-kelas";

    protected static ?string $pluralModelLabel = 'Data Ruangan';

    public static function form(Schema $schema): Schema
    {
        return RefRuangForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefRuangsTable::configure($table);
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
            'index' => ListRefRuangs::route('/'),
        ];
    }
}
