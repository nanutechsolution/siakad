<?php

namespace App\Filament\Resources\DosenKetersediaans;

use App\Enums\NavigationGroup;
use App\Filament\Resources\DosenKetersediaans\Pages\CreateDosenKetersediaan;
use App\Filament\Resources\DosenKetersediaans\Pages\EditDosenKetersediaan;
use App\Filament\Resources\DosenKetersediaans\Pages\ListDosenKetersediaans;
use App\Filament\Resources\DosenKetersediaans\Schemas\DosenKetersediaanForm;
use App\Filament\Resources\DosenKetersediaans\Tables\DosenKetersediaansTable;
use App\Models\DosenKetersediaan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DosenKetersediaanResource extends Resource
{
    protected static ?string $model = DosenKetersediaan::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Ketersediaan Dosen';

    protected static string|UnitEnum| null $navigationGroup = NavigationGroup::MASTER->value;

    protected static ?string $modelLabel = 'Ketersediaan Dosen';

    protected static ?string $pluralModelLabel = 'Ketersediaan Dosen';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return DosenKetersediaanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DosenKetersediaansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    /**
     * Selalu eager-load relasi yang dibutuhkan untuk menampilkan nama dosen
     * (person + gelar) agar kolom/select tidak N+1.
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->with(['dosen.person.gelars']);
    }
    public static function getPages(): array
    {
        return [
            'index' => ListDosenKetersediaans::route('/'),
        ];
    }
}
