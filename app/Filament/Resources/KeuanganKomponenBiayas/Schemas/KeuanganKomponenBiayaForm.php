<?php

namespace App\Filament\Resources\KeuanganKomponenBiayas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KeuanganKomponenBiayaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Komponen Biaya')
                    ->description('Master komponen biaya yang digunakan pada penyusunan skema tarif mahasiswa.')
                    ->schema([
                        TextInput::make('nama_komponen')
                            ->label('Nama Komponen')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Contoh: SPP Tetap'),

                        Select::make('tipe_biaya')
                            ->label('Tipe Biaya')
                            ->options([
                                'TETAP' => 'Tetap',
                                'SKS' => 'Per SKS',
                                'SEKALI' => 'Sekali',
                                'INSIDENTAL' => 'Insidental',
                            ])
                            ->required()
                            ->native(false)
                            ->helperText('Menentukan cara perhitungan komponen biaya.'),

                        TextInput::make('urutan_prioritas')
                            ->label('Urutan Prioritas')
                            ->numeric()
                            ->default(99)
                            ->required()
                            ->minValue(1)
                            ->helperText('Semakin kecil nilainya, semakin tinggi prioritas penagihan.'),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->inline(false),

                    ])->columnSpanFull()
            ]);
    }
}
