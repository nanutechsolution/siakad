<?php

namespace App\Filament\Dosen\Resources\MahasiswaBimbingans;

use App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages\CreateMahasiswaBimbingan;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages\EditMahasiswaBimbingan;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages\ListMahasiswaBimbingans;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Pages\ViewMahasiswaBimbingan;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Schemas\MahasiswaBimbinganForm;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Schemas\MahasiswaBimbinganInfolist;
use App\Filament\Dosen\Resources\MahasiswaBimbingans\Tables\MahasiswaBimbingansTable;
use App\Models\Mahasiswa;
use App\Models\TrxDosen;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MahasiswaBimbinganResource extends Resource
{
    protected static ?string $model = Mahasiswa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected static ?string $navigationLabel = 'Bimbingan Akademik';
    protected static ?string $modelLabel = 'Mahasiswa Bimbingan';
    
    
    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canView($record): bool
    {
        return true;
    }
    public static function getEloquentQuery(): Builder
    {
        $dosen = TrxDosen::where('person_id', Auth::user()->person_id)->first();

        // Dosen hanya melihat mahasiswa yang kelasnya memiliki dosen wali tersebut
        return parent::getEloquentQuery()
            ->whereHas('kelas', function ($query) use ($dosen) {
                $query->whereHas('kelasDosenWalis', function ($q) use ($dosen) {
                    $q->where('dosen_id', $dosen?->id ?? 0);
                });
            });
    }
    public static function form(Schema $schema): Schema
    {
        return MahasiswaBimbinganForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MahasiswaBimbinganInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MahasiswaBimbingansTable::configure($table);
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
            'index' => ListMahasiswaBimbingans::route('/'),
            'create' => CreateMahasiswaBimbingan::route('/create'),
            'view' => ViewMahasiswaBimbingan::route('/{record}'),
            'edit' => EditMahasiswaBimbingan::route('/{record}/edit'),
        ];
    }
}
