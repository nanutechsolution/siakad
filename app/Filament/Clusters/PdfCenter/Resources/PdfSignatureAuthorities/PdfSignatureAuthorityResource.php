<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities;

use App\Filament\Clusters\PdfCenter\PdfCenterCluster;
use App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\Pages\CreatePdfSignatureAuthority;
use App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\Pages\EditPdfSignatureAuthority;
use App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\Pages\ListPdfSignatureAuthorities;
use App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\Schemas\PdfSignatureAuthorityForm;
use App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\Tables\PdfSignatureAuthoritiesTable;
use App\Models\PdfSignatureAuthority;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PdfSignatureAuthorityResource extends Resource
{
    protected static ?string $model = PdfSignatureAuthority::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?string $navigationLabel = 'Penandatangan';

    protected static ?string $modelLabel = 'Otoritas Penandatangan';

    protected static ?int $navigationSort = 3;
    protected static ?string $cluster = PdfCenterCluster::class;

    public static function form(Schema $schema): Schema
    {
        return PdfSignatureAuthorityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PdfSignatureAuthoritiesTable::configure($table);
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
            'index' => ListPdfSignatureAuthorities::route('/'),
            'create' => CreatePdfSignatureAuthority::route('/create'),
            'edit' => EditPdfSignatureAuthority::route('/{record}/edit'),
        ];
    }
}
