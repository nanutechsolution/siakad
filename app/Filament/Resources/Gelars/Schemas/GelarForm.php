<?php

namespace App\Filament\Resources\Gelars\Schemas;

use App\Enums\HR\JenjangGelar;
use App\Enums\HR\PosisiGelar;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class GelarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode')
                    ->label('Kode Gelar (Singkatan)')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Misal: S.Kom., M.T., Dr.'),
                Select::make('posisi')
                    ->label('Posisi Penulisan')
                    ->required()
                    ->options(PosisiGelar::class)
                    ->live(),
                TextInput::make('nama')
                    ->label('Nama Lengkap Gelar')
                    ->required()
                    ->maxLength(255)
                    ->live(debounce: 500)
                    ->hint(function (Get $get) {
                        $kode = $get('kode') ?: '...';
                        $posisi = $get('posisi');
                        if (!$posisi) return null;

                        return $posisi === PosisiGelar::DEPAN->value
                            ? "Preview: {$kode} [Nama Lengkap]"
                            : "Preview: [Nama Lengkap], {$kode}";
                    })
                    ->hintColor('primary'),
                Select::make('jenjang')
                    ->label('Jenjang Akademik')
                    ->required()
                    ->options(JenjangGelar::class),
            ])->columns(1);
    }
}
