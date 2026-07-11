<?php

namespace App\Filament\Resources\KeuanganSkemaTarifs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KeuanganSkemaTarifForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Section::make('Informasi Skema Tarif')
                    ->description('Konfigurasi skema tarif berdasarkan angkatan, program studi, dan program kelas.')
                    ->schema([

                        TextInput::make('nama_skema')
                            ->label('Nama Skema')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Contoh: Reguler 2025'),

                        Select::make('angkatan_id')
                            ->label('Angkatan')
                            ->relationship(
                                name: 'angkatan',
                                titleAttribute: 'id_tahun'
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('prodi_id')
                            ->label('Program Studi')
                            ->relationship(
                                name: 'prodi',
                                titleAttribute: 'nama_prodi'
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('program_kelas_id')
                            ->label('Program Kelas')
                            ->relationship(
                                name: 'programKelas',
                                titleAttribute: 'nama_program'
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),

                    ])
                    ->columns(2),
            ]);
    }
}
