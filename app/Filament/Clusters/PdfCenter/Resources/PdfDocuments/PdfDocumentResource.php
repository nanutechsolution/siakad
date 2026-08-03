<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfDocuments;

use App\Filament\Clusters\PdfCenter\PdfCenterCluster;
use App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\Pages\ListPdfDocuments;
use App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\Pages\ViewPdfDocument;
use App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\Schemas\PdfDocumentForm;
use App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\Schemas\PdfDocumentInfolist;
use App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\Tables\PdfDocumentsTable;
use App\Models\PdfDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

class PdfDocumentResource extends Resource
{
    protected static ?string $model = PdfDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Arsip Dokumen';

    protected static ?string $modelLabel = 'Dokumen PDF';

    protected static ?int $navigationSort = 2;
    protected static ?string $cluster = PdfCenterCluster::class;

    #[Override]
    public static function canCreate(): bool
    {
        return false;
    }
    #[Override]
    public static function canEdit(Model $record): bool
    {
        return false;
    }
    public static function form(Schema $schema): Schema
    {
        return PdfDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PdfDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PdfDocumentsTable::configure($table);
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
            'index' => ListPdfDocuments::route('/'),
            'view' => ViewPdfDocument::route('/{record}'),
        ];
    }
}
