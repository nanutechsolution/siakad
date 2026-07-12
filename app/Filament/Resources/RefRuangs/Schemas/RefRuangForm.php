<?php

namespace App\Filament\Resources\RefRuangs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RefRuangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Dasar Ruangan')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('kode_ruang')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(20)
                                ->helperText('Contoh: R-TI-101'),

                            TextInput::make('nama_ruang')
                                ->required()
                                ->maxLength(100),
                        ]),

                        TextInput::make('kapasitas')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(40),
                    ]),

                Section::make('Konfigurasi Absensi Geospasial')
                    ->description('Gunakan koordinat GPS untuk validasi absensi mahasiswa.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('latitude')
                                ->numeric()
                                ->step(0.00000001)
                                ->placeholder('-7.12345678'),

                            TextInput::make('longitude')
                                ->numeric()
                                ->step(0.00000001)
                                ->placeholder('110.12345678'),

                            TextInput::make('radius_meter')
                                ->label('Radius Absensi (meter)')
                                ->numeric()
                                ->minValue(5)
                                ->maxValue(500)
                                ->default(50)
                                ->suffix('meters'),
                        ]),
                    ]),
            ]);
    }
}
