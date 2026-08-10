<?php

namespace App\Filament\Clusters\ManajemenKelas\Resources\Manajemenkelas;

use App\Filament\Clusters\ManajemenKelas\ManajemenKelasCluster;
use App\Filament\Clusters\ManajemenKelas\Resources\Manajemenkelas\Pages\ListManajemenkelas;
use App\Filament\Clusters\ManajemenKelas\Resources\Manajemenkelas\Schemas\ManajemenkelasForm;
use App\Filament\Clusters\ManajemenKelas\Resources\Manajemenkelas\Tables\ManajemenkelasTable;
use App\Models\Kelas;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManajemenkelasResource extends Resource
{
    protected static ?string $model = Kelas::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $cluster = ManajemenKelasCluster::class;
    protected static ?string $navigationLabel = 'Data Kelas';
    protected static ?string $modelLabel = 'Kelas';
    protected static ?int $navigationSort = 1;
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user());
    }
    public static function form(Schema $schema): Schema
    {
        return ManajemenkelasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ManajemenkelasTable::configure($table);
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
            'index' => ListManajemenkelas::route('/'),
        ];
    }
}
