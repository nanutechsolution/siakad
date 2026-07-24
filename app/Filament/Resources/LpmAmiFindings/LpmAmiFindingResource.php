<?php

namespace App\Filament\Resources\LpmAmiFindings;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmAmiFindings\Pages\CreateLpmAmiFinding;
use App\Filament\Resources\LpmAmiFindings\Pages\EditLpmAmiFinding;
use App\Filament\Resources\LpmAmiFindings\Pages\ListLpmAmiFindings;
use App\Filament\Resources\LpmAmiFindings\RelationManagers\DiscussionsRelationManager;
use App\Filament\Resources\LpmAmiFindings\RelationManagers\EvidencesRelationManager;
use App\Filament\Resources\LpmAmiFindings\Schemas\LpmAmiFindingForm;
use App\Filament\Resources\LpmAmiFindings\Tables\LpmAmiFindingsTable;
use App\Models\LpmAmiFinding;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LpmAmiFindingResource extends Resource
{
    protected static ?string $model = LpmAmiFinding::class;
    protected static ?string $navigationLabel = 'Temuan Audit';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    protected static ?string $modelLabel = 'Temuan Audit';
    public static function form(Schema $schema): Schema
    {
        return LpmAmiFindingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmAmiFindingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            EvidencesRelationManager::class,
            DiscussionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLpmAmiFindings::route('/'),
            'create' => CreateLpmAmiFinding::route('/create'),
            'edit' => EditLpmAmiFinding::route('/{record}/edit'),
        ];
    }
}
