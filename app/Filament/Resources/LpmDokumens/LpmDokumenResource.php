<?php

namespace App\Filament\Resources\LpmDokumens;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmDokumens\Pages\CreateLpmDokumen;
use App\Filament\Resources\LpmDokumens\Pages\EditLpmDokumen;
use App\Filament\Resources\LpmDokumens\Pages\ListLpmDokumens;
use App\Filament\Resources\LpmDokumens\RelationManagers\ApprovalsRelationManager;
use App\Filament\Resources\LpmDokumens\RelationManagers\VersiBaruRelationManager;
use App\Filament\Resources\LpmDokumens\Schemas\LpmDokumenForm;
use App\Filament\Resources\LpmDokumens\Tables\LpmDokumensTable;
use App\Models\LpmDokumen;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LpmDokumenResource extends Resource
{
    protected static ?string $model = LpmDokumen::class;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    protected static ?string $navigationLabel = 'Dokumen Mutu';

    protected static ?string $modelLabel = 'Dokumen Mutu';
    public static function form(Schema $schema): Schema
    {
        return LpmDokumenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmDokumensTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ApprovalsRelationManager::class,
            VersiBaruRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpmDokumens::route('/'),
            'create' => CreateLpmDokumen::route('/create'),
            'edit' => EditLpmDokumen::route('/{record}/edit'),
        ];
    }
}
