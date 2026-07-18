<?php

namespace App\Filament\Resources\Khs;

use App\Enums\NavigationGroup;
use App\Filament\Resources\Khs\Pages\CreateKhs;
use App\Filament\Resources\Khs\Pages\EditKhs;
use App\Filament\Resources\Khs\Pages\ListKhs;
use App\Filament\Resources\Khs\Pages\ViewKhs;
use App\Filament\Resources\Khs\Schemas\KhsForm;
use App\Filament\Resources\Khs\Schemas\KhsInfolist;
use App\Filament\Resources\Khs\Tables\KhsTable;
use App\Filament\Resources\Krs\RelationManagers\KrsDetailsRelationManager;
use App\Models\Khs;
use App\Models\Krs;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KhsResource extends Resource
{
    protected static ?string $model = Krs::class;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::AKADEMIK->value;
    protected static ?string $navigationLabel = 'Kartu Hasil Studi';
    protected static ?string $modelLabel = 'KHS';
    protected static ?string $pluralModelLabel = 'Kartu Hasil Studi (KHS)';
    protected static ?string $recordTitleAttribute = 'Kartu Hasil Studi';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
    public static function form(Schema $schema): Schema
    {
        return KhsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KhsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KhsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            KrsDetailsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKhs::route('/'),
            'view' => ViewKhs::route('/{record}'),
        ];
    }
}
