<?php

namespace App\Filament\Resources\PersonRoles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PersonRoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_role')
                    ->label('Kode Role')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Misal: DOSEN_TETAP, TENDIK, KAPRODI'),
                TextInput::make('nama_role')
                    ->label('Nama Role Institusi')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Misal: Dosen Tetap Yayasan'),
            ])->columns(1);
    }
}
