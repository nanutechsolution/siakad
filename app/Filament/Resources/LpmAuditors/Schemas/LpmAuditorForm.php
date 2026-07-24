<?php

namespace App\Filament\Resources\LpmAuditors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LpmAuditorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('person_id')
                    ->label('Nama')
                    ->relationship('person', 'nama_lengkap')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('no_sertifikat_auditor')
                    ->label('No. Sertifikat Auditor')
                    ->maxLength(100),
                Textarea::make('kompetensi')
                    ->label('Kompetensi')
                    ->rows(3)
                    ->helperText('Ringkasan pelatihan/kompetensi audit mutu yang dimiliki.'),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }
}
