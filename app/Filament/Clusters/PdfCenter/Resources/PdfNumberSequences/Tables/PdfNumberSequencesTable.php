<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfNumberSequences\Tables;

use App\Enums\Pdf\PdfDocumentType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PdfNumberSequencesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_type')
                    ->label('Jenis Dokumen')
                    ->formatStateUsing(fn(string $state) => PdfDocumentType::from($state)->label())
                    ->searchable(),
                TextColumn::make('kode_unit')->label('Kode Unit'),
                TextColumn::make('periode_tahun')->label('Tahun'),
                TextColumn::make('last_sequence')->label('Nomor Terakhir')->alignCenter(),
                TextColumn::make('format_template')->label('Format')->fontFamily('mono'),
            ])
            ->defaultSort('periode_tahun', 'desc');
    }
}
