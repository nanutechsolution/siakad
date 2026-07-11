<?php

namespace App\Filament\Resources\MasterKurikulums;

use App\Enums\NavigationGroup;
use App\Filament\Resources\MasterKurikulums\Pages\CreateMasterKurikulum;
use App\Filament\Resources\MasterKurikulums\Pages\EditMasterKurikulum;
use App\Filament\Resources\MasterKurikulums\Pages\ListMasterKurikulums;
use App\Filament\Resources\MasterKurikulums\Schemas\MasterKurikulumForm;
use App\Filament\Resources\MasterKurikulums\Tables\MasterKurikulumsTable;
use App\Models\MasterKurikulum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MasterKurikulumResource extends Resource
{
    protected static ?string $model = MasterKurikulum::class;

    protected static ?string $slug = 'master-akademik/kurikulum';
    protected static ?string $recordTitleAttribute = 'nama_kurikulum';
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::MASTER->value;
    }
    public static function getPluralLabel(): ?string
    {
        return 'Data Kurikulum';
    }
    public static function form(Schema $schema): Schema
    {
        return MasterKurikulumForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterKurikulumsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\KurikulumKomponenNilaiRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterKurikulums::route('/'),
            'create' => CreateMasterKurikulum::route('/create'),
            'edit' => EditMasterKurikulum::route('/{record}/edit'),
        ];
    }
}
