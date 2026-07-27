<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences;

use App\Filament\Clusters\PdfCenter\PdfCenterCluster;
use App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences\Pages\CreatePdfNumberSequence;
use App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences\Pages\EditPdfNumberSequence;
use App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences\Pages\ListPdfNumberSequences;
use App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences\Schemas\PdfNumberSequenceForm;
use App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences\Tables\PdfNumberSequencesTable;
use App\Models\PdfNumberSequence;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

class PdfNumberSequenceResource extends Resource
{
    protected static ?string $model = PdfNumberSequence::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static ?string $navigationLabel = 'Nomor Dokumen';

    protected static ?string $modelLabel = 'Sequence Nomor';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }
    #[Override]
    public static function canEdit(Model $record): bool
    {
        return false;
    }
    protected static ?string $cluster = PdfCenterCluster::class;

    public static function form(Schema $schema): Schema
    {
        return PdfNumberSequenceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PdfNumberSequencesTable::configure($table);
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
            'index' => ListPdfNumberSequences::route('/'),
        ];
    }
}
