<?php

namespace App\Filament\Resources\DispensasiAkademiks;

use App\Filament\Resources\DispensasiAkademiks\Pages\CreateDispensasiAkademik;
use App\Filament\Resources\DispensasiAkademiks\Pages\EditDispensasiAkademik;
use App\Filament\Resources\DispensasiAkademiks\Pages\ListDispensasiAkademiks;
use App\Filament\Resources\DispensasiAkademiks\Schemas\DispensasiAkademikForm;
use App\Filament\Resources\DispensasiAkademiks\Tables\DispensasiAkademiksTable;
use App\Models\DispensasiAkademik;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DispensasiAkademikResource extends Resource
{
    protected static ?string $model = DispensasiAkademik::class;

    protected static string|UnitEnum|null $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Dispensasi Akademik';
    protected static ?string $modelLabel = 'Dispensasi Akademik';
    protected static ?string $pluralModelLabel = 'Dispensasi Akademik';
    public static function form(Schema $schema): Schema
    {
        return DispensasiAkademikForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DispensasiAkademiksTable::configure($table);
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
            'index' => ListDispensasiAkademiks::route('/'),
            'create' => CreateDispensasiAkademik::route('/create'),
            'edit' => EditDispensasiAkademik::route('/{record}/edit'),
        ];
    }
}
