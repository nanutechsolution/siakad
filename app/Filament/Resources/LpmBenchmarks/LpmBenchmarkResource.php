<?php

namespace App\Filament\Resources\LpmBenchmarks;

use App\Enums\NavigationGroup;
use App\Filament\Resources\LpmBenchmarks\Pages\CreateLpmBenchmark;
use App\Filament\Resources\LpmBenchmarks\Pages\EditLpmBenchmark;
use App\Filament\Resources\LpmBenchmarks\Pages\ListLpmBenchmarks;
use App\Filament\Resources\LpmBenchmarks\Schemas\LpmBenchmarkForm;
use App\Filament\Resources\LpmBenchmarks\Tables\LpmBenchmarksTable;
use App\Models\LpmBenchmark;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LpmBenchmarkResource extends Resource
{
    protected static ?string $model = LpmBenchmark::class;

    protected static ?string $navigationLabel = 'Benchmarking';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::LPM->value;
    protected static ?string $modelLabel = 'Data Benchmarking';
    public static function form(Schema $schema): Schema
    {
        return LpmBenchmarkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LpmBenchmarksTable::configure($table);
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
            'index' => ListLpmBenchmarks::route('/'),
            'create' => CreateLpmBenchmark::route('/create'),
            'edit' => EditLpmBenchmark::route('/{record}/edit'),
        ];
    }
}
