<?php

namespace App\Filament\Clusters\ManajemenKelas\Resources\Manajemenkelas\Schemas;

use App\Models\RefAngkatan;
use App\Models\RefProgram;
use App\Support\Utf8;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManajemenkelasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Kelas')
                    ->columns(2)
                    ->columnSpanFull()

                    ->components([
                        TextInput::make('nama_kelas')
                            ->label('Nama Kelas')
                            ->required()
                            ->maxLength(255),
                        Select::make('prodi_id')
                            ->label('Program Studi')
                            ->relationship('prodi', 'nama_prodi')
                            ->getOptionLabelFromRecordUsing(fn($record) => Utf8::clean($record->nama_prodi))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('program_id')
                            ->label('Program')
                            ->options(fn() => RefProgram::query()->where('is_active', true)->orderBy('nama_program')->pluck('nama_program', 'id')->map(fn(?string $n) => Utf8::clean($n)))
                            ->searchable()
                            ->required(),
                        Select::make('angkatan_id')
                            ->label('Angkatan')
                            ->options(fn() => RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                            ->searchable()
                            ->required(),
                        TextInput::make('kapasitas')
                            ->label('Kapasitas')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Kosongkan kalau tidak ingin membatasi jumlah mahasiswa per kelas.'),
                    ]),
            ]);
    }
}
