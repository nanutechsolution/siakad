<?php

namespace App\Filament\Resources\PembayaranMahasiswas;

use App\Enums\NavigationGroup;
use App\Filament\Resources\PembayaranMahasiswas\Pages\CreatePembayaranMahasiswa;
use App\Filament\Resources\PembayaranMahasiswas\Pages\EditPembayaranMahasiswa;
use App\Filament\Resources\PembayaranMahasiswas\Pages\ListPembayaranMahasiswas;
use App\Filament\Resources\PembayaranMahasiswas\Schemas\PembayaranMahasiswaForm;
use App\Filament\Resources\PembayaranMahasiswas\Tables\PembayaranMahasiswasTable;
use App\Models\PembayaranMahasiswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PembayaranMahasiswaResource extends Resource
{
    protected static ?string $model = PembayaranMahasiswa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;
    protected static ?string $navigationLabel = 'Verifikasi Pembayaran Mahasiswa';
    public static function form(Schema $schema): Schema
    {
        return PembayaranMahasiswaForm::configure($schema);
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status_verifikasi_id', 1)->count() ?: null;
    }

    public static function table(Table $table): Table
    {
        return PembayaranMahasiswasTable::configure($table);
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
            'index' => ListPembayaranMahasiswas::route('/'),
            'create' => CreatePembayaranMahasiswa::route('/create'),
            'edit' => EditPembayaranMahasiswa::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
