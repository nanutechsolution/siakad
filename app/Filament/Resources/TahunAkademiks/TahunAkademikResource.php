<?php

namespace App\Filament\Resources\TahunAkademiks;

use App\Enums\NavigationGroup;
use App\Filament\Resources\TahunAkademiks\Pages\CreateTahunAkademik;
use App\Filament\Resources\TahunAkademiks\Pages\EditTahunAkademik;
use App\Filament\Resources\TahunAkademiks\Pages\ListTahunAkademiks;
use App\Filament\Resources\TahunAkademiks\Pages\ViewTahunAkademik;
use App\Filament\Resources\TahunAkademiks\Schemas\TahunAkademikForm;
use App\Filament\Resources\TahunAkademiks\Schemas\TahunAkademikInfolist;
use App\Filament\Resources\TahunAkademiks\Tables\TahunAkademiksTable;
use App\Models\RefTahunAkademik;
use App\Models\TahunAkademik;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TahunAkademikResource extends Resource
{
    protected static ?string $model = RefTahunAkademik::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected static ?string $navigationLabel = 'Kalender Akademik';
    protected static ?string $modelLabel = 'Kalender Akademik';
    protected static ?string $pluralModelLabel = 'Kalender Akademik';
    protected static ?string $recordTitleAttribute = 'nama_tahun';
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::MASTER->value;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_tahun', 'nama_tahun'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::activeSemester()?->status->getLabel();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::activeSemester()?->status->getColor();
    }

    protected static function activeSemester(): ?RefTahunAkademik
    {
        $id = cache()->remember(
            'nav.tahun-akademik.active.id',
            now()->addMinute(),
            fn() => RefTahunAkademik::query()
                ->where('is_active', true)
                ->latest('kode_tahun')
                ->value('id'),
        );

        return $id ? RefTahunAkademik::find($id) : null;
    }

    public static function form(Schema $schema): Schema
    {
        return TahunAkademikForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TahunAkademikInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TahunAkademiksTable::configure($table);
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
            'index' => ListTahunAkademiks::route('/'),
            'create' => CreateTahunAkademik::route('/create'),
            'view' => ViewTahunAkademik::route('/{record}'),
            'edit' => EditTahunAkademik::route('/{record}/edit'),
        ];
    }
}
