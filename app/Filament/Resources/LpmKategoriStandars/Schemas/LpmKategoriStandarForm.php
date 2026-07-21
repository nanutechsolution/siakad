<?php

namespace App\Filament\Resources\LpmKategoriStandars\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LpmKategoriStandarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode')
                    ->label('Kode')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true)
                    ->helperText('Contoh: PENDIDIKAN, PENELITIAN, PENGABDIAN, TAMBAHAN'),
                TextInput::make('nama')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(150),
                TextInput::make('urutan')
                    ->label('Urutan')
                    ->numeric()
                    ->default(1)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
