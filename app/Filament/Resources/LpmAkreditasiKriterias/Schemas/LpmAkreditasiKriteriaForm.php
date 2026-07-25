<?php

namespace App\Filament\Resources\LpmAkreditasiKriterias\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LpmAkreditasiKriteriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('akreditasi_id')
                    ->label('Proses Akreditasi')
                    ->relationship('akreditasi', 'id')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->lembaga?->nama} - {$record->jenis_akreditasi}")
                    ->required(),
                TextInput::make('kode_kriteria')
                    ->label('Kode Kriteria')
                    ->required()
                    ->maxLength(20),
                TextInput::make('nama_kriteria')
                    ->label('Nama Kriteria')
                    ->required()
                    ->maxLength(255),
                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(1)
                    ->required(),
            ]);
    }
}
