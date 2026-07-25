<?php

namespace App\Filament\Resources\LpmBenchmarkInstitusis\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LpmBenchmarkInstitusiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_institusi')
                    ->label('Nama Institusi')
                    ->required()
                    ->maxLength(255),
                TextInput::make('jenis')
                    ->label('Jenis')
                    ->maxLength(50)
                    ->helperText('Contoh: PTN, PTS, Internasional'),
                Textarea::make('catatan')
                    ->label('Catatan')
                    ->rows(3),
            ]);
    }
}
