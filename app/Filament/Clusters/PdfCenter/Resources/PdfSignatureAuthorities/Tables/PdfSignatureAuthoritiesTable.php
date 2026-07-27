<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\Tables;

use App\Enums\Pdf\PdfDocumentType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PdfSignatureAuthoritiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_type')
                    ->label('Jenis Dokumen')
                    ->formatStateUsing(fn(string $state) => PdfDocumentType::from($state)->label())
                    ->searchable(),
                TextColumn::make('jabatan.nama_jabatan')->label('Jabatan'),
                TextColumn::make('label')->label('Label Tercetak'),
                TextColumn::make('urutan')->label('Urutan')->alignCenter(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->defaultSort('document_type')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
