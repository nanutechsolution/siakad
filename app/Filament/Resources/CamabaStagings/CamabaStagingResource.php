<?php

namespace App\Filament\Resources\CamabaStagings;

use App\Enums\NavigationGroup;
use App\Filament\Resources\CamabaStagings\Pages\CreateCamabaStaging;
use App\Filament\Resources\CamabaStagings\Pages\EditCamabaStaging;
use App\Filament\Resources\CamabaStagings\Pages\ListCamabaStagings;
use App\Filament\Resources\CamabaStagings\Schemas\CamabaStagingForm;
use App\Filament\Resources\CamabaStagings\Tables\CamabaStagingsTable;
use App\Models\PmbCamabaStaging;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CamabaStagingResource extends Resource
{
    protected static ?string $model = PmbCamabaStaging::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup =  NavigationGroup::INTEGRASI->value;
    protected static ?string $modelLabel = 'Log Kiriman PMB';      
    protected static ?string $pluralModelLabel = 'Log Kiriman PMB'; 
    public static function form(Schema $schema): Schema
    {
        return CamabaStagingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CamabaStagingsTable::configure($table);
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
            'index' => ListCamabaStagings::route('/'),
            'create' => CreateCamabaStaging::route('/create'),
            'edit' => EditCamabaStaging::route('/{record}/edit'),
        ];
    }
}
