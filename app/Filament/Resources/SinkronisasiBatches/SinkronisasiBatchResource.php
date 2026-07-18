<?php

namespace App\Filament\Resources\SinkronisasiBatches;

use App\Enums\NavigationGroup;
use App\Filament\Resources\SinkronisasiBatches\Pages\CreateSinkronisasiBatch;
use App\Filament\Resources\SinkronisasiBatches\Pages\EditSinkronisasiBatch;
use App\Filament\Resources\SinkronisasiBatches\Pages\ListSinkronisasiBatches;
use App\Filament\Resources\SinkronisasiBatches\Pages\ViewSinkronisasiBatch;
use App\Filament\Resources\SinkronisasiBatches\Schemas\SinkronisasiBatchForm;
use App\Filament\Resources\SinkronisasiBatches\Schemas\SinkronisasiBatchInfolist;
use App\Filament\Resources\SinkronisasiBatches\Tables\SinkronisasiBatchesTable;
use App\Models\SinkronisasiBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SinkronisasiBatchResource extends Resource
{
    protected static ?string $model = SinkronisasiBatch::class;

    protected static ?string $navigationLabel = 'Riwayat Sinkronisasi';
    protected static ?string $modelLabel = 'Batch Sinkronisasi';
    protected static ?int $navigationSort = 5;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;


    public static function form(Schema $schema): Schema
    {
        return SinkronisasiBatchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SinkronisasiBatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SinkronisasiBatchesTable::configure($table);
    }
    public static function canCreate(): bool
    {
        return false;
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
            'index' => ListSinkronisasiBatches::route('/'),
            'create' => CreateSinkronisasiBatch::route('/create'),
            'view' => ViewSinkronisasiBatch::route('/{record}'),
            'edit' => EditSinkronisasiBatch::route('/{record}/edit'),
        ];
    }
}
