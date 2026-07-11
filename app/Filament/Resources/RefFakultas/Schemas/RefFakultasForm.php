<?php

namespace App\Filament\Resources\RefFakultas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RefFakultasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Fakultas')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('kode_fakultas')
                                    ->label('Kode Fakultas')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(10)
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase']),

                                TextInput::make('id_feeder')
                                    ->label('ID Feeder (PDDikti)')
                                    ->maxLength(36)
                                    ->nullable()
                                    ->helperText('UUID dari PDDikti jika tersinkronisasi.'),
                            ]),
                        TextInput::make('nama_fakultas')
                            ->label('Nama Fakultas')
                            ->required()
                            ->maxLength(100)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
