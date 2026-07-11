<?php

namespace App\Filament\Resources\MasterBeasiswas\Schemas;

use App\Enums\Keuangan\KategoriBeasiswa;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class MasterBeasiswasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('nama_beasiswa')
                            ->label('Nama Program Beasiswa')
                            ->required()
                            ->maxLength(150)
                            ->placeholder('Misal: Beasiswa Prestasi Yayasan 2026')
                            ->columnSpanFull(),

                        Select::make('kategori')
                            ->label('Kategori Sumber')
                            ->options(KategoriBeasiswa::class)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->inline(false)
                            ->required(),

                        Textarea::make('keterangan')
                            ->label('Keterangan / Dasar Hukum')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
