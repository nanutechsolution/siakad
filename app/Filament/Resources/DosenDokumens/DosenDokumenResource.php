<?php

namespace App\Filament\Resources\DosenDokumens;

use App\Enums\NavigationGroup;
use App\Filament\Resources\DosenDokumens\Pages\CreateDosenDokumen;
use App\Filament\Resources\DosenDokumens\Pages\EditDosenDokumen;
use App\Filament\Resources\DosenDokumens\Pages\ListDosenDokumens;
use App\Filament\Resources\DosenDokumens\Schemas\DosenDokumenForm;
use App\Filament\Resources\DosenDokumens\Tables\DosenDokumensTable;
use App\Models\DosenDokumen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DosenDokumenResource extends Resource
{
    protected static ?string $model = DosenDokumen::class;
    protected static ?string $navigationLabel = 'Verifikasi Dokumen Dosen';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEPEGAWAIAN->value;
    protected static ?string $modelLabel = 'Dokumen Dosen';
    protected static ?string $pluralModelLabel = 'Dokumen Dosen';

    public static function form(Schema $schema): Schema
    {
        return DosenDokumenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DosenDokumensTable::configure($table);
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
            'index' => ListDosenDokumens::route('/'),
        ];
    }


    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
