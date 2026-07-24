<?php

namespace App\Filament\Resources\LpmAkreditasiLembagas;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmAkreditasiLembagas\Pages\CreateLpmAkreditasiLembaga;
use App\Filament\Resources\LpmAkreditasiLembagas\Pages\EditLpmAkreditasiLembaga;
use App\Filament\Resources\LpmAkreditasiLembagas\Pages\ListLpmAkreditasiLembagas;
use App\Filament\Resources\LpmAkreditasiLembagas\Schemas\LpmAkreditasiLembagaForm;
use App\Filament\Resources\LpmAkreditasiLembagas\Tables\LpmAkreditasiLembagasTable;
use App\Models\LpmAkreditasiLembaga;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LpmAkreditasiLembagaResource extends Resource
{
    protected static ?string $model = LpmAkreditasiLembaga::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Lembaga Akreditasi';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    protected static ?string $modelLabel = 'Lembaga Akreditasi';
    public static function form(Schema $schema): Schema
    {
        return LpmAkreditasiLembagaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmAkreditasiLembagasTable::configure($table);
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
            'index' => ListLpmAkreditasiLembagas::route('/'),
        ];
    }
}
