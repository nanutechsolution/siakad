<?php

namespace App\Filament\Resources\LpmAkreditasiLembagas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LpmAkreditasiLembagaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode')
                    ->label('Kode')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true)
                    ->helperText('Contoh: BANPT, LAMTEKNIK, LAMPTKES'),
                TextInput::make('nama')
                    ->label('Nama Lembaga')
                    ->required()
                    ->maxLength(150),
                Select::make('jenis')
                    ->label('Jenis Akreditasi')
                    ->options([
                        'INSTITUSI' => 'Institusi',
                        'PRODI' => 'Program Studi',
                    ])
                    ->default('PRODI')
                    ->required(),
            ]);
    }
}
