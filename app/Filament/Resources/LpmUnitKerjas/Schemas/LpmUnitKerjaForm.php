<?php

namespace App\Filament\Resources\LpmUnitKerjas\Schemas;

use App\Models\RefFakultas;
use App\Models\RefProdi;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LpmUnitKerjaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('jenis_unit')
                    ->label('Jenis Unit')
                    ->options([
                        'UNIVERSITAS' => 'Universitas',
                        'FAKULTAS' => 'Fakultas',
                        'PRODI' => 'Program Studi',
                        'LEMBAGA' => 'Lembaga',
                        'BIRO' => 'Biro',
                        'UPT' => 'UPT',
                    ])
                    ->required()
                    ->live(),
                TextInput::make('kode_unit')
                    ->label('Kode Unit')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true),
                TextInput::make('nama_unit')
                    ->label('Nama Unit')
                    ->required()
                    ->maxLength(255),
                Select::make('parent_id')
                    ->label('Unit Induk')
                    ->relationship('parent', 'nama_unit')
                    ->searchable()
                    ->preload(),
                Select::make('fakultas_id')
                    ->label('Referensi Fakultas')
                    ->helperText('Isi hanya jika unit ini merepresentasikan Fakultas yang sudah ada di data akademik.')
                    ->options(fn() => RefFakultas::query()->pluck('nama_fakultas', 'id'))
                    ->searchable()
                    ->visible(fn(Get $get) => $get('jenis_unit') === 'FAKULTAS'),
                Select::make('prodi_id')
                    ->label('Referensi Program Studi')
                    ->helperText('Isi hanya jika unit ini merepresentasikan Program Studi yang sudah ada di data akademik.')
                    ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                    ->searchable()
                    ->visible(fn(Get $get) => $get('jenis_unit') === 'PRODI'),
                Select::make('kepala_unit_person_id')
                    ->label('Kepala Unit')
                    ->relationship('kepalaUnit', 'nama_lengkap')
                    ->searchable()
                    ->preload(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
