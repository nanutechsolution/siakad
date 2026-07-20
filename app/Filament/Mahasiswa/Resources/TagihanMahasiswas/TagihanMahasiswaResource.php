<?php

namespace App\Filament\Mahasiswa\Resources\TagihanMahasiswas;

use App\Enums\MahasiswaNavigationGroup;
use App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Pages\ListTagihanMahasiswas;
use App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Pages\ViewTagihanMahasiswa;
use App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Schemas\TagihanMahasiswaForm;
use App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Schemas\TagihanMahasiswaInfolist;
use App\Filament\Mahasiswa\Resources\TagihanMahasiswas\Tables\TagihanMahasiswasTable;
use App\Models\Mahasiswa;
use App\Models\TagihanMahasiswa;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class TagihanMahasiswaResource extends Resource
{
    protected static ?string $model = TagihanMahasiswa::class;
    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::KEMAHASISWAAN->value;
    protected static ?string $navigationLabel = 'Tagihan Semester';
    protected static ?string $modelLabel = 'Tagihan Semester';
    protected static ?string $pluralModelLabel = 'Daftar Tagihan Semester';
    /**
     * Memotong query dari hulu agar mahasiswa HANYA bisa melihat tagihan miliknya sendiri.
     */
    public static function getEloquentQuery(): Builder
    {
        $mahasiswa = Mahasiswa::where(
            'person_id',
            Auth::user()->person_id
        )->first();

        return parent::getEloquentQuery()
            ->with([
                'details',
                'pembayaran',
                'tahunAkademik',
            ])
            ->where('mahasiswa_id', $mahasiswa?->id ?? 0);
    }
    public static function form(Schema $schema): Schema
    {
        return TagihanMahasiswaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TagihanMahasiswaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagihanMahasiswasTable::configure($table);
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
            'index' => ListTagihanMahasiswas::route('/'),
            'view' => ViewTagihanMahasiswa::route('/{record}'),
        ];
    }

    public static function canViewAny(): bool
    {
        return true;
    }
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
