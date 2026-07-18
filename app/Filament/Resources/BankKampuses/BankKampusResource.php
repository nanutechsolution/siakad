<?php

namespace App\Filament\Resources\BankKampuses;

use App\Enums\NavigationGroup;
use App\Filament\Resources\BankKampuses\Pages\ListBankKampuses;
use App\Filament\Resources\BankKampuses\Schemas\BankKampusForm;
use App\Filament\Resources\BankKampuses\Tables\BankKampusesTable;
use App\Models\BankKampus;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class BankKampusResource extends Resource
{
    protected static ?string $model = BankKampus::class;
    protected static ?string $navigationLabel = 'Rekening Bank';
    protected static ?int $navigationSort = 4;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;
    public static function form(Schema $schema): Schema
    {
        return BankKampusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankKampusesTable::configure($table);
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
            'index' => ListBankKampuses::route('/'),
        ];
    }
}
