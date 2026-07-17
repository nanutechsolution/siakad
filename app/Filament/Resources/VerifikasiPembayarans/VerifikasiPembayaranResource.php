<?php

namespace App\Filament\Resources\VerifikasiPembayarans;

use App\Enums\NavigationGroup;
use App\Enums\StatusVerifikasiPembayaran;
use App\Filament\Resources\VerifikasiPembayarans\Pages\CreateVerifikasiPembayaran;
use App\Filament\Resources\VerifikasiPembayarans\Pages\EditVerifikasiPembayaran;
use App\Filament\Resources\VerifikasiPembayarans\Pages\ListVerifikasiPembayarans;
use App\Filament\Resources\VerifikasiPembayarans\Schemas\VerifikasiPembayaranForm;
use App\Filament\Resources\VerifikasiPembayarans\Tables\VerifikasiPembayaransTable;
use App\Models\PembayaranMahasiswa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;
use UnitEnum;

class VerifikasiPembayaranResource extends Resource
{
    protected static ?string $model = PembayaranMahasiswa::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::KEUANGAN->value;
    protected static ?string $navigationLabel = 'Verifikasi Pembayaran';
    protected static ?string $modelLabel = 'Pembayaran Masuk';
    protected static ?string $pluralModelLabel = 'Verifikasi Pembayaran Masuk';
    protected static ?string $slug = 'verifikasi-pembayaran';
    protected static ?int $navigationSort = 2;


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status_verifikasi_id', StatusVerifikasiPembayaran::PENDING)->count() ?: null;
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
    public static function form(Schema $schema): Schema
    {
        return VerifikasiPembayaranForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VerifikasiPembayaransTable::configure($table);
    }
    #[Override]
    public static function canView(Model $record): bool
    {
        return false;
    }

    #[Override]
    public static function canEdit(Model $record): bool
    {
        return false;
    }
    #[Override]
    public static function canCreate(): bool
    {
        return false;
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
            'index' => ListVerifikasiPembayarans::route('/'),
        ];
    }
}
