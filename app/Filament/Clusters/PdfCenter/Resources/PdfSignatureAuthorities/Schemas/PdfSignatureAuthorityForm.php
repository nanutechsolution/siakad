<?php

namespace App\Filament\Clusters\PdfCenter\Resources\PdfSignatureAuthorities\Schemas;

use App\Enums\Pdf\PdfDocumentType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PdfSignatureAuthorityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options(collect(PdfDocumentType::cases())
                        ->mapWithKeys(fn($case) => [$case->value => $case->label()])
                        ->all())
                    ->required(),

                Select::make('jabatan_id')
                    ->label('Jabatan')
                    ->relationship('jabatan', 'nama_jabatan')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('label')
                    ->label('Label Tercetak')
                    ->helperText('Teks yang tampil di atas nama penandatangan, mis. "Mengetahui, Kepala BAAK"')
                    ->required()
                    ->maxLength(150),

                TextInput::make('urutan')
                    ->label('Urutan Tanda Tangan')
                    ->numeric()
                    ->default(1)
                    ->required(),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
