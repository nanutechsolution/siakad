<?php

namespace App\Filament\Clusters\Migration\Resources\MigrationHistories;

use App\Filament\Clusters\Migration\MigrationCluster;
use App\Filament\Clusters\Migration\Resources\MigrationHistories\Pages\ListMigrationHistories;
use App\Filament\Clusters\Migration\Resources\MigrationHistories\Pages\ViewMigrationHistory;
use App\Filament\Clusters\Migration\Resources\MigrationHistories\Schemas\MigrationHistoryForm;
use App\Filament\Clusters\Migration\Resources\MigrationHistories\Schemas\MigrationHistoryInfolist;
use App\Filament\Clusters\Migration\Resources\MigrationHistories\Tables\MigrationHistoriesTable;
use App\Models\MigrationBatch;
use App\Models\MigrationHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MigrationHistoryResource extends Resource
{
    protected static ?string $model = MigrationBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Riwayat Migrasi';

    protected static ?string $modelLabel = 'Riwayat Migrasi';

    protected static ?string $pluralModelLabel = 'Riwayat Migrasi';

    protected static ?int $navigationSort = 3;

    protected static ?string $cluster = MigrationCluster::class;

    public static function form(Schema $schema): Schema
    {
        return MigrationHistoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MigrationHistoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MigrationHistoriesTable::configure($table);
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
            'index' => ListMigrationHistories::route('/'),
            'view' => ViewMigrationHistory::route('/{record}'),
        ];
    }
}
