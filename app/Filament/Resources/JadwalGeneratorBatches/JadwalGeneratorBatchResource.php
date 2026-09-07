<?php

namespace App\Filament\Resources\JadwalGeneratorBatches;

use App\Enums\NavigationGroup;
use App\Filament\Resources\JadwalGeneratorBatches\Pages\CreateJadwalGeneratorBatch;
use App\Filament\Resources\JadwalGeneratorBatches\Pages\EditJadwalGeneratorBatch;
use App\Filament\Resources\JadwalGeneratorBatches\Pages\ListJadwalGeneratorBatches;
use App\Filament\Resources\JadwalGeneratorBatches\Pages\ViewJadwalGeneratorBatch;
use App\Filament\Resources\JadwalGeneratorBatches\RelationManagers\ResultsRelationManager;
use App\Filament\Resources\JadwalGeneratorBatches\Schemas\JadwalGeneratorBatchForm;
use App\Filament\Resources\JadwalGeneratorBatches\Schemas\JadwalGeneratorBatchInfolist;
use App\Filament\Resources\JadwalGeneratorBatches\Tables\JadwalGeneratorBatchesTable;
use App\Models\JadwalGeneratorBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JadwalGeneratorBatchResource extends Resource
{
    protected static ?string $model = JadwalGeneratorBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;
    protected static ?string $navigationLabel = 'Generator Jadwal';
    
    protected static string|\UnitEnum|null $navigationGroup =  NavigationGroup::PERKULIAHAN->value;
    public static function form(Schema $schema): Schema
    {
        return JadwalGeneratorBatchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JadwalGeneratorBatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JadwalGeneratorBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
           ResultsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJadwalGeneratorBatches::route('/'),
            'create' => CreateJadwalGeneratorBatch::route('/create'),
            'view' => ViewJadwalGeneratorBatch::route('/{record}'),
            'edit' => EditJadwalGeneratorBatch::route('/{record}/edit'),
        ];
    }
}
