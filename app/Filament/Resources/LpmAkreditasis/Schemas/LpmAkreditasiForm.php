<?php

namespace App\Filament\Resources\LpmAkreditasis\Schemas;

use App\Models\RefProdi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LpmAkreditasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('lembaga_id')
                    ->label('Lembaga Akreditasi')
                    ->relationship('lembaga', 'nama')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),
                Select::make('jenis_akreditasi')
                    ->label('Jenis Akreditasi')
                    ->options([
                        'INSTITUSI' => 'Institusi',
                        'PRODI' => 'Program Studi',
                    ])
                    ->required()
                    ->live(),
                Select::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                    ->searchable()
                    ->visible(fn(Get $get) => $get('jenis_akreditasi') === 'PRODI')
                    ->required(fn(Get $get) => $get('jenis_akreditasi') === 'PRODI'),
                TextInput::make('instrumen')
                    ->label('Instrumen')
                    ->maxLength(100)
                    ->helperText('Contoh: IAPS 4.0, IAPT 3.0'),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'PERSIAPAN' => 'Persiapan',
                        'PENGISIAN' => 'Pengisian Borang',
                        'SUBMIT' => 'Sudah Submit',
                        'VISITASI' => 'Visitasi',
                        'SELESAI' => 'Selesai',
                    ])
                    ->default('PERSIAPAN')
                    ->required(),
                TextInput::make('peringkat_target')
                    ->label('Peringkat Target')
                    ->maxLength(50),
                TextInput::make('peringkat_hasil')
                    ->label('Peringkat Hasil')
                    ->maxLength(50),
                DatePicker::make('tanggal_submit')->label('Tanggal Submit'),
                DatePicker::make('tanggal_visitasi')->label('Tanggal Visitasi'),
                DatePicker::make('berlaku_sampai')->label('Berlaku Sampai'),
            ]);
    }
}
