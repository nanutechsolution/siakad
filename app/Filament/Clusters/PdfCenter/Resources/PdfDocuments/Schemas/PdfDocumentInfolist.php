<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfDocuments\Schemas;

use App\Enums\Pdf\PdfDocumentType;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PdfDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dokumen')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('document_type')
                            ->label('Jenis Dokumen')
                            ->formatStateUsing(fn(string $state) => PdfDocumentType::from($state)->label()),
                        TextEntry::make('nomor_dokumen')->label('Nomor Dokumen')->placeholder('—'),
                        TextEntry::make('classification')->label('Klasifikasi'),
                        TextEntry::make('status')->label('Status'),
                        TextEntry::make('version')->label('Versi'),
                        TextEntry::make('file_hash')->label('SHA-256 Hash')->copyable(),
                        TextEntry::make('generated_at')->label('Diterbitkan Pada')->dateTime('d F Y H:i'),
                    ]),

                Section::make('Riwayat Tanda Tangan')
                    ->schema([
                        RepeatableEntry::make('signatures')
                            ->label('')
                            ->schema([
                                TextEntry::make('jabatan_snapshot')->label('Jabatan'),
                                TextEntry::make('nama_penandatangan_snapshot')->label('Nama'),
                                TextEntry::make('signed_at')->label('Ditandatangani Pada')->dateTime('d F Y H:i'),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }
}
