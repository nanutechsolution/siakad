<?php

namespace App\Filament\Resources\RefPrograms\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RefProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Program')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('nama_program')
                                ->required()
                                ->maxLength(50)
                                ->placeholder('Contoh: Reguler Pagi'),

                            TextInput::make('kode_internal')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(10)
                                ->placeholder('Contoh: REG'),
                        ]),

                        TextInput::make('id_jenis_kelas_feeder')
                            ->label('ID Feeder (PDDIKTI)')
                            ->helperText('Diperlukan untuk sinkronisasi data ke Feeder PDDIKTI.')
                            ->maxLength(255),

                        Textarea::make('deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Program Aktif')
                            ->default(true)
                            ->onColor('success'),
                    ])
            ]);
    }
}
