<?php

namespace App\Filament\Resources\RefAturanSks;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RefAturanSks\Pages\CreateRefAturanSks;
use App\Filament\Resources\RefAturanSks\Pages\EditRefAturanSks;
use App\Filament\Resources\RefAturanSks\Pages\ListRefAturanSks;
use App\Filament\Resources\RefAturanSks\Schemas\RefAturanSksForm;
use App\Filament\Resources\RefAturanSks\Tables\RefAturanSksTable;
use App\Models\RefAturanSks;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RefAturanSksResource extends Resource
{
    protected static ?string $model = RefAturanSks::class;


    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::MASTER->value;

    protected static ?string $modelLabel = 'Aturan SKS';

    protected static ?string $pluralModelLabel = 'Aturan SKS';

    public static function form(Schema $schema): Schema
    {
        return RefAturanSksForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefAturanSksTable::configure($table);
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
            'index' => ListRefAturanSks::route('/'),
        ];
    }
}
