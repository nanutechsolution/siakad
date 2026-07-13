<?php

namespace App\Filament\Resources\BankKampuses\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BankKampusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_bank')
                    ->label('Nama Bank')
                    ->required()
                    ->placeholder('Contoh: BNI / Bank Mandiri')
                    ->maxLength(255),

                TextInput::make('no_rekening')
                    ->label('Nomor Rekening')
                    ->required()
                    ->numeric() // Memastikan hanya angka
                    ->maxLength(255),

                TextInput::make('atas_nama')
                    ->label('Atas Nama')
                    ->required()
                    ->placeholder('Contoh: Yayasan UNMARIS')
                    ->maxLength(255),

                FileUpload::make('logo')
                    ->label('Logo Bank')
                    ->image()
                    ->directory('logo-bank')
                    ->maxSize(1024), // Max 1MB

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->helperText('Matikan jika rekening ini sudah tidak digunakan untuk menerima pembayaran mahasiswa.'),
            ]);
    }
}
