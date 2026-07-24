<?php

namespace App\Filament\Resources\LpmAmiChecklists\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LpmAmiChecklistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('standar_id')
                    ->label('Standar')
                    ->relationship('standar', 'nama_standar')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('kriteria')
                    ->label('Kriteria Audit')
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
