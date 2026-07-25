<?php

namespace App\Filament\Resources\LpmBenchmarks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LpmBenchmarkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('indikator_id')
                    ->label('Indikator')
                    ->relationship('indikator', 'nama_indikator')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('institusi_pembanding_id')
                    ->label('Institusi Pembanding')
                    ->relationship('institusiPembanding', 'nama_institusi')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('tahun')
                    ->label('Tahun')
                    ->numeric()
                    ->required()
                    ->default(now()->year),
                TextInput::make('nilai_internal')
                    ->label('Nilai Internal (Universitas Kita)')
                    ->numeric()
                    ->step(0.01),
                TextInput::make('nilai_eksternal')
                    ->label('Nilai Eksternal (Institusi Pembanding)')
                    ->numeric()
                    ->step(0.01),
                TextInput::make('sumber_data')
                    ->label('Sumber Data')
                    ->maxLength(255)
                    ->helperText('Contoh: Laporan PDDIKTI 2025, Website resmi institusi, dsb.'),
                Textarea::make('analisis_gap')
                    ->label('Analisis Gap')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
