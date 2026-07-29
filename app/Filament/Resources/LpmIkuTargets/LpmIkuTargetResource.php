<?php

namespace App\Filament\Resources\LpmIkuTargets;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmIkuTargets\Pages\CreateLpmIkuTarget;
use App\Filament\Resources\LpmIkuTargets\Pages\EditLpmIkuTarget;
use App\Filament\Resources\LpmIkuTargets\Pages\ListLpmIkuTargets;
use App\Filament\Resources\LpmIkuTargets\RelationManagers\BuktiPelaksanaansRelationManager;
use App\Filament\Resources\LpmIkuTargets\Schemas\LpmIkuTargetForm;
use App\Filament\Resources\LpmIkuTargets\Tables\LpmIkuTargetsTable;
use App\Models\LpmIkuTarget;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class LpmIkuTargetResource extends Resource
{
    protected static ?string $model = LpmIkuTarget::class;
    protected static ?string $navigationLabel = 'Target & Capaian IKU';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    protected static ?string $modelLabel = 'Target IKU';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return LpmIkuTargetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmIkuTargetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            BuktiPelaksanaansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpmIkuTargets::route('/'),
            'create' => CreateLpmIkuTarget::route('/create'),
            'edit' => EditLpmIkuTarget::route('/{record}/edit'),
        ];
    }
}
