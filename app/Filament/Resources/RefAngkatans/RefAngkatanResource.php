<?php

namespace App\Filament\Resources\RefAngkatans;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RefAngkatans\Pages\CreateRefAngkatan;
use App\Filament\Resources\RefAngkatans\Pages\EditRefAngkatan;
use App\Filament\Resources\RefAngkatans\Pages\ListRefAngkatans;
use App\Filament\Resources\RefAngkatans\Schemas\RefAngkatanForm;
use App\Filament\Resources\RefAngkatans\Tables\RefAngkatansTable;
use App\Models\RefAngkatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RefAngkatanResource extends Resource
{
    protected static ?string $model = RefAngkatan::class;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::MASTER->value;

    protected static ?string $modelLabel = 'Tahun Angkatan';

    protected static ?string $pluralModelLabel = 'Tahun Angkatan';

    public static function form(Schema $schema): Schema
    {
        return RefAngkatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefAngkatansTable::configure($table);
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
            'index' => ListRefAngkatans::route('/'),
            'create' => CreateRefAngkatan::route('/create'),
            'edit' => EditRefAngkatan::route('/{record}/edit'),
        ];
    }
}
