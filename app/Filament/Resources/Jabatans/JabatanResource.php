<?php

namespace App\Filament\Resources\Jabatans;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Jabatans\Pages\CreateJabatan;
use App\Filament\Resources\Jabatans\Pages\EditJabatan;
use App\Filament\Resources\Jabatans\Pages\ListJabatans;
use App\Filament\Resources\Jabatans\Schemas\JabatanForm;
use App\Filament\Resources\Jabatans\Tables\JabatansTable;
use App\Models\RefJabatan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class JabatanResource extends Resource
{
    protected static ?string $model = RefJabatan::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEPEGAWAIAN->value;
    protected static ?string $modelLabel = 'Master Jabatan';

    protected static ?string $pluralModelLabel = 'Master Jabatan';
    protected static ?int $navigationSort = 1;
    public static function form(Schema $schema): Schema
    {
        return JabatanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JabatansTable::configure($table);
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
            'index' => ListJabatans::route('/'),
        ];
    }
}
