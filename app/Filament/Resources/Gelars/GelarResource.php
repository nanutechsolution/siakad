<?php

namespace App\Filament\Resources\Gelars;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Gelars\Pages\CreateGelar;
use App\Filament\Resources\Gelars\Pages\EditGelar;
use App\Filament\Resources\Gelars\Pages\ListGelars;
use App\Filament\Resources\Gelars\Schemas\GelarForm;
use App\Filament\Resources\Gelars\Tables\GelarsTable;
use App\Models\RefGelar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class GelarResource extends Resource
{
    protected static ?string $model = RefGelar::class;

    // protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static string|UnitEnum|null $navigationGroup =NavigationGroup::KEPEGAWAIAN->value;
    protected static ?string $modelLabel = 'Master Gelar';
    protected static ?string $pluralModelLabel = 'Master Gelar';
    protected static ?int $navigationSort = 2;
    public static function form(Schema $schema): Schema
    {
        return GelarForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GelarsTable::configure($table);
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
            'index' => ListGelars::route('/'),
        ];
    }
}
