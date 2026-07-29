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
                Select::make('scope')
                    ->label('Scope Pencocokan')
                    ->options([
                        'NONE' => 'Institusi (satu untuk semua, mis. Rektor/BAUK)',
                        'PRODI' => 'Per Program Studi (cocokkan ke prodi mahasiswa)',
                        'FAKULTAS' => 'Per Fakultas (cocokkan ke fakultas mahasiswa)',
                    ])
                    ->default('NONE')
                    ->required()
                    ->helperText('Pilih PRODI/FAKULTAS jika jabatan ini dipegang berbeda-beda orang per unit (mis. tiap prodi punya Kaprodi sendiri).'),
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
