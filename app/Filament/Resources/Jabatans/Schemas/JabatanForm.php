<?php

namespace App\Filament\Resources\Jabatans\Schemas;

use App\Enums\HR\JenisJabatan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class JabatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_jabatan')
                    ->label('Kode Jabatan')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true),
                TextInput::make('nama_jabatan')
                    ->label('Nama Jabatan')
                    ->required()
                    ->maxLength(100),
                Select::make('jenis')
                    ->label('Jenis Jabatan')
                    ->required()
                    ->options(JenisJabatan::class),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ])->columns(1);
    }
}
