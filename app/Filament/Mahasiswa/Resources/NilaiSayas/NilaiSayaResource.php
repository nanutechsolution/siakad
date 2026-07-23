<?php

namespace App\Filament\Mahasiswa\Resources\NilaiSayas;

use App\Enums\MahasiswaNavigationGroup;
use App\Filament\Mahasiswa\Resources\NilaiSayas\Pages\CreateNilaiSaya;
use App\Filament\Mahasiswa\Resources\NilaiSayas\Pages\EditNilaiSaya;
use App\Filament\Mahasiswa\Resources\NilaiSayas\Pages\ListNilaiSayas;
use App\Filament\Mahasiswa\Resources\NilaiSayas\Schemas\NilaiSayaForm;
use App\Filament\Mahasiswa\Resources\NilaiSayas\Tables\NilaiSayasTable;
use App\Models\KrsDetail;
use App\Services\Mahasiswa\NilaiAkademikService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class NilaiSayaResource extends Resource
{
    protected static ?string $model = KrsDetail::class;
    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::NILAI->value;
    protected static ?string $navigationLabel = 'Nilai Saya';
    protected static ?string $modelLabel = 'Nilai';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return app(NilaiAkademikService::class)->nilaiSayaQuery();
    }
    public static function form(Schema $schema): Schema
    {
        return NilaiSayaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NilaiSayasTable::configure($table);
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
            'index' => ListNilaiSayas::route('/'),
        ];
    }
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
