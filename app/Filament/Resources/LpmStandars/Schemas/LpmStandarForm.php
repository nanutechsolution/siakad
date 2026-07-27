<?php

namespace App\Filament\Resources\LpmStandars\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LpmStandarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kategori_standar_id')
                    ->label('Kategori Standar')
                    ->relationship('kategoriStandar', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('kategori')
                    ->label('Kategori (Akademik/Non-Akademik)')
                    ->options([
                        'AKADEMIK' => 'Akademik',
                        'NON-AKADEMIK' => 'Non-Akademik',
                    ])
                    ->required(),
                TextInput::make('kode_standar')
                    ->label('Kode Standar')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('nama_standar')
                    ->label('Nama Standar')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Textarea::make('pernyataan_standar')
                    ->label('Pernyataan Standar')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('target_pencapaian')
                    ->label('Target Pencapaian')
                    ->numeric()
                    ->default(100)
                    ->required(),
                TextInput::make('satuan')
                    ->label('Satuan')
                    ->default('%')
                    ->required(),
                TextInput::make('versi')
                    ->label('Versi')
                    ->numeric()
                    ->default(1)
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Naikkan versi lewat tombol "Tingkatkan Standar", bukan diedit langsung, supaya riwayat perubahannya tercatat.'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
