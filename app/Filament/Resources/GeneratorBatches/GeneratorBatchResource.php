<?php

namespace App\Filament\Resources\GeneratorBatches;

use App\Enums\NavigationGroup;
use App\Filament\Resources\GeneratorBatches\Pages\ListGeneratorBatches;
use App\Filament\Resources\GeneratorBatches\Pages\ViewGeneratorBatch;
use App\Filament\Resources\GeneratorBatches\RelationManagers\LogsRelationManager;
use App\Filament\Resources\GeneratorBatches\Schemas\GeneratorBatchForm;
use App\Filament\Resources\GeneratorBatches\Schemas\GeneratorBatchInfolist;
use App\Filament\Resources\GeneratorBatches\Tables\GeneratorBatchesTable;
use App\Models\GeneratorBatch;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;
use UnitEnum;

class GeneratorBatchResource extends Resource
{
    protected static ?string $model = GeneratorBatch::class;
    protected static ?string $navigationLabel = 'Riwayat Generator';
    protected static ?string $modelLabel = 'Batch Generator';
    // protected static ?int $navigationSort = 6;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;
    public static function form(Schema $schema): Schema
    {
        return GeneratorBatchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GeneratorBatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeneratorBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LogsRelationManager::class
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }
    #[Override]
    public static function canEdit(Model $record): bool
    {
        return false;
    }
    public static function getPages(): array
    {
        return [
            'index' => ListGeneratorBatches::route('/'),
            'view' => ViewGeneratorBatch::route('/{record}'),
        ];
    }
}
