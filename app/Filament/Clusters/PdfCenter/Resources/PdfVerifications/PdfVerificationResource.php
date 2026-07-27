<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfVerifications;

use App\Filament\Clusters\PdfCenter\PdfCenterCluster;
use App\Filament\Clusters\PdfCenter\Resources\PdfVerifications\Pages\CreatePdfVerification;
use App\Filament\Clusters\PdfCenter\Resources\PdfVerifications\Pages\EditPdfVerification;
use App\Filament\Clusters\PdfCenter\Resources\PdfVerifications\Pages\ListPdfVerifications;
use App\Filament\Clusters\PdfCenter\Resources\PdfVerifications\Schemas\PdfVerificationForm;
use App\Filament\Clusters\PdfCenter\Resources\PdfVerifications\Tables\PdfVerificationsTable;
use App\Models\PdfVerification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

class PdfVerificationResource extends Resource
{
    protected static ?string $model = PdfVerification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;
    protected static ?string $navigationLabel = 'Log Verifikasi';

    protected static ?string $modelLabel = 'Verifikasi QR';

    protected static ?int $navigationSort = 5;

    protected static ?string $cluster = PdfCenterCluster::class;
    public static function canCreate(): bool
    {
        return false;
    }
    #[Override]
    public static function canEdit(Model $record): bool
    {
        return false;
    }
    #[Override]
    public static function canDelete(Model $record): bool
    {
        return false;
    }
    public static function form(Schema $schema): Schema
    {
        return PdfVerificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PdfVerificationsTable::configure($table);
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
            'index' => ListPdfVerifications::route('/'),
            'create' => CreatePdfVerification::route('/create'),
            'edit' => EditPdfVerification::route('/{record}/edit'),
        ];
    }
}
