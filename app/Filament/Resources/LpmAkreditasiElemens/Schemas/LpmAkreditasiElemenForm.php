<?php

namespace App\Filament\Resources\LpmAkreditasiElemens\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LpmAkreditasiElemenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kriteria_id')
                    ->label('Kriteria')
                    ->relationship('kriteria', 'nama_kriteria')
                    ->required(),
                TextInput::make('kode_elemen')
                    ->label('Kode Elemen')
                    ->required()
                    ->maxLength(20),
                Textarea::make('deskripsi')
                    ->label('Deskripsi Butir')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('status_kelengkapan')
                    ->label('Status Kelengkapan')
                    ->options([
                        'BELUM' => 'Belum',
                        'PROSES' => 'Proses',
                        'LENGKAP' => 'Lengkap',
                    ])
                    ->required(),
            ]);
    }
}
