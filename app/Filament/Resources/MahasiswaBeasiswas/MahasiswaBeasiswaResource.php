<?php

namespace App\Filament\Resources\MahasiswaBeasiswas;

use App\Enums\NavigationGroup;
use App\Filament\Resources\MahasiswaBeasiswas\Pages\CreateMahasiswaBeasiswa;
use App\Filament\Resources\MahasiswaBeasiswas\Pages\EditMahasiswaBeasiswa;
use App\Filament\Resources\MahasiswaBeasiswas\Pages\ListMahasiswaBeasiswas;
use App\Filament\Resources\MahasiswaBeasiswas\Schemas\MahasiswaBeasiswaForm;
use App\Filament\Resources\MahasiswaBeasiswas\Tables\MahasiswaBeasiswasTable;
use App\Models\KeuanganMahasiswaBeasiswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MahasiswaBeasiswaResource extends Resource
{
    protected static ?string $model = KeuanganMahasiswaBeasiswa::class;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::BEASISWA->value;
    protected static ?string $modelLabel = 'Pemberian Beasiswa';
    protected static ?string $pluralModelLabel = 'Pemberian Beasiswa';
    protected static ?int $navigationSort = 5;
    public static function getEloquentQuery(): Builder
    {
        // Eager loading agresif untuk mencegah N+1 query yang masif di tabel
        return parent::getEloquentQuery()
            ->with([
                'mahasiswa.person',
                'beasiswa',
                'tahunAkademikMulai',
                'tahunAkademikAkhir'
            ]);
    }
    public static function form(Schema $schema): Schema
    {
        return MahasiswaBeasiswaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MahasiswaBeasiswasTable::configure($table);
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
            'index' => ListMahasiswaBeasiswas::route('/'),
            // 'create' => CreateMahasiswaBeasiswa::route('/create'),
            // 'edit' => EditMahasiswaBeasiswa::route('/{record}/edit'),
        ];
    }
}
