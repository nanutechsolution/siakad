<?php

namespace App\Filament\Mahasiswa\Resources\RiwayatKrs;

use App\Enums\MahasiswaNavigationGroup;
use App\Filament\Mahasiswa\Resources\RiwayatKrs\Pages\CreateRiwayatKrs;
use App\Filament\Mahasiswa\Resources\RiwayatKrs\Pages\EditRiwayatKrs;
use App\Filament\Mahasiswa\Resources\RiwayatKrs\Pages\ListRiwayatKrs;
use App\Filament\Mahasiswa\Resources\RiwayatKrs\Pages\ViewRiwayatKrs;
use App\Filament\Mahasiswa\Resources\RiwayatKrs\Schemas\RiwayatKrsForm;
use App\Filament\Mahasiswa\Resources\RiwayatKrs\Schemas\RiwayatKrsInfolist;
use App\Filament\Mahasiswa\Resources\RiwayatKrs\Tables\RiwayatKrsTable;
use App\Models\Krs;
use App\Models\Mahasiswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

class RiwayatKrsResource extends Resource
{
    protected static ?string $model = Krs::class;
    protected static string|UnitEnum|null $navigationGroup = MahasiswaNavigationGroup::KRS->value;
    protected static ?string $navigationLabel = 'Riwayat KRS';
    protected static ?string $modelLabel = 'Riwayat KRS';
    protected static ?string $slug = 'riwayat-krs';
    protected static ?int $navigationSort = 2;

    /**
     * MEMBATASI DATA: Mahasiswa HANYA bisa melihat KRS miliknya sendiri
     */
    public static function getEloquentQuery(): Builder
    {
        $mahasiswa = Mahasiswa::where('person_id', Auth::user()->person_id)->first();

        return parent::getEloquentQuery()
            ->where('mahasiswa_id', $mahasiswa?->id)
            ->with(['tahunAkademik']); // Eager load untuk performa tabel
    }

    /**
     * KUNCI KEAMANAN: Read-Only Mode
     */
    #[Override]
    public static function canViewAny(): bool
    {

        return true;
    }
    /**
     * KUNCI KEAMANAN: Read-Only Mode
     */
    public static function canCreate(): bool
    {
        return false;
    }

    #[Override]
    public static function canView(Model $record): bool
    {
        return true;
    }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
    public static function form(Schema $schema): Schema
    {
        return RiwayatKrsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RiwayatKrsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RiwayatKrsTable::configure($table);
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
            'index' => ListRiwayatKrs::route('/'),
            'view' => ViewRiwayatKrs::route('/{record}'),
        ];
    }
}
