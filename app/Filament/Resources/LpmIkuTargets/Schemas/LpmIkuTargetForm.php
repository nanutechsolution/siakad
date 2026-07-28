<?php

namespace App\Filament\Resources\LpmIkuTargets\Schemas;

use App\Models\RefProdi;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LpmIkuTargetForm
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
                Select::make('prodi_id')
                    ->label('Program Studi (opsional)')
                    ->options(fn() => RefProdi::query()->pluck('nama_prodi', 'id'))
                    ->searchable()
                    ->helperText('Kosongkan kalau target ini level institusi/unit non-prodi.'),
                Select::make('unit_kerja_id')
                    ->label('Unit Kerja (opsional)')
                    ->relationship('unitKerja', 'nama_unit')
                    ->searchable()
                    ->preload(),
                TextInput::make('tahun')
                    ->label('Tahun')
                    ->numeric()
                    ->default(now()->year)
                    ->required(),
                TextInput::make('target_nilai')
                    ->label('Target Nilai')
                    ->numeric()
                    ->step(0.01)
                    ->required(),
                TextInput::make('capaian_nilai')
                    ->label('Capaian Nilai')
                    ->numeric()
                    ->step(0.01)
                    ->default(0)
                    ->required(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'DRAFT' => 'Draft',
                        'SUBMITTED' => 'Submitted',
                        'VALIDATED' => 'Validated',
                    ])
                    ->default('DRAFT')
                    ->required(),
                Textarea::make('analisis_kendala')
                    ->label('Analisis Kendala')
                    ->rows(2)
                    ->columnSpanFull(),
                Textarea::make('tindakan_koreksi')
                    ->label('Tindakan Koreksi')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }
}
