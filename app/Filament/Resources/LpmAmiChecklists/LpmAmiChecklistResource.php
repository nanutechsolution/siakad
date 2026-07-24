<?php

namespace App\Filament\Resources\LpmAmiChecklists;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmAmiChecklists\Pages\CreateLpmAmiChecklist;
use App\Filament\Resources\LpmAmiChecklists\Pages\EditLpmAmiChecklist;
use App\Filament\Resources\LpmAmiChecklists\Pages\ListLpmAmiChecklists;
use App\Filament\Resources\LpmAmiChecklists\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\LpmAmiChecklists\Schemas\LpmAmiChecklistForm;
use App\Filament\Resources\LpmAmiChecklists\Tables\LpmAmiChecklistsTable;
use App\Models\LpmAmiChecklist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LpmAmiChecklistResource extends Resource
{
    protected static ?string $model = LpmAmiChecklist::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Checklist Audit';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    protected static ?string $modelLabel = 'Checklist Audit';
    public static function form(Schema $schema): Schema
    {
        return LpmAmiChecklistForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmAmiChecklistsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpmAmiChecklists::route('/'),
            'create' => CreateLpmAmiChecklist::route('/create'),
            'edit' => EditLpmAmiChecklist::route('/{record}/edit'),
        ];
    }
}
