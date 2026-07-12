<?php

namespace App\Filament\Resources\KurikulumMataKuliahs;

use App\Enums\NavigationGroup;
use App\Filament\Resources\KurikulumMataKuliahs\Pages\CreateKurikulumMataKuliah;
use App\Filament\Resources\KurikulumMataKuliahs\Pages\EditKurikulumMataKuliah;
use App\Filament\Resources\KurikulumMataKuliahs\Pages\ListKurikulumMataKuliahs;
use App\Filament\Resources\KurikulumMataKuliahs\Schemas\KurikulumMataKuliahForm;
use App\Filament\Resources\KurikulumMataKuliahs\Tables\KurikulumMataKuliahsTable;
use App\Models\KurikulumMataKuliah;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class KurikulumMataKuliahResource extends Resource
{
    protected static ?string $model = KurikulumMataKuliah::class;

    protected static ?string $slug = 'master-akademik/kurikulum-mata-kuliah';
    protected static ?string $modelLabel = 'Pemetaan MK Kurikulum';
    protected static ?string $pluralModelLabel = 'Pemetaan Mata Kuliah';
    protected static ?int $navigationSort = 5;
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::MASTER->value;
    }
    public static function form(Schema $schema): Schema
    {
        return KurikulumMataKuliahForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KurikulumMataKuliahsTable::configure($table);
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
            'index' => ListKurikulumMataKuliahs::route('/'),
            'create' => CreateKurikulumMataKuliah::route('/create'),
            'edit' => EditKurikulumMataKuliah::route('/{record}/edit'),
        ];
    }
}
