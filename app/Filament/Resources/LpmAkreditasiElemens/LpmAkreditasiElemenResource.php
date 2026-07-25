<?php

namespace App\Filament\Resources\LpmAkreditasiElemens;

use App\Filament\Resources\LpmAkreditasiElemens\Pages\CreateLpmAkreditasiElemen;
use App\Filament\Resources\LpmAkreditasiElemens\Pages\EditLpmAkreditasiElemen;
use App\Filament\Resources\LpmAkreditasiElemens\Pages\ListLpmAkreditasiElemens;
use App\Filament\Resources\LpmAkreditasiElemens\RelationManagers\IndikatorsRelationManager;
use App\Filament\Resources\LpmAkreditasiElemens\Schemas\LpmAkreditasiElemenForm;
use App\Filament\Resources\LpmAkreditasiElemens\Tables\LpmAkreditasiElemensTable;
use App\Filament\Resources\LpmAkreditasiElemens\RelationManagers\EvidencesRelationManager;
use App\Models\LpmAkreditasiElemen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LpmAkreditasiElemenResource extends Resource
{
    protected static ?string $model = LpmAkreditasiElemen::class;
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Schema $schema): Schema
    {
        return LpmAkreditasiElemenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmAkreditasiElemensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            IndikatorsRelationManager::class,
            EvidencesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpmAkreditasiElemens::route('/'),
            'create' => CreateLpmAkreditasiElemen::route('/create'),
            'edit' => EditLpmAkreditasiElemen::route('/{record}/edit'),
        ];
    }
}
