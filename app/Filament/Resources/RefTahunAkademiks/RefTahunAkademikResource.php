<?php

namespace App\Filament\Resources\RefTahunAkademiks;

use App\Enums\NavigationGroup;
use App\Filament\Resources\RefTahunAkademiks\Pages\CreateRefTahunAkademik;
use App\Filament\Resources\RefTahunAkademiks\Pages\EditRefTahunAkademik;
use App\Filament\Resources\RefTahunAkademiks\Pages\ListRefTahunAkademiks;
use App\Filament\Resources\RefTahunAkademiks\Schemas\RefTahunAkademikForm;
use App\Filament\Resources\RefTahunAkademiks\Tables\RefTahunAkademiksTable;
use App\Models\RefTahunAkademik;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RefTahunAkademikResource extends Resource
{
    protected static ?string $model = RefTahunAkademik::class;

    protected static ?string $modelLabel = 'Tahun Akademik';
    protected static ?string $pluralModelLabel = 'Tahun Akademik';

    protected static ?int $navigationSort = 4;
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::MASTER->value;
    }

    protected static ?string $recordTitleAttribute = 'nama_prodi';

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_prodi_internal', 'kode_prodi_dikti', 'nama_prodi'];
    }

    public static function form(Schema $schema): Schema
    {
        return RefTahunAkademikForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefTahunAkademiksTable::configure($table);
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
            'index' => ListRefTahunAkademiks::route('/'),
            'create' => CreateRefTahunAkademik::route('/create'),
            'edit' => EditRefTahunAkademik::route('/{record}/edit'),
        ];
    }
}
