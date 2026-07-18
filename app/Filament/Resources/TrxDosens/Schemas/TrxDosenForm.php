<?php

namespace App\Filament\Resources\TrxDosens\Schemas;

use App\Domain\Authorization\Services\FormResolver;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TrxDosenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('person_id')
                    ->label('Nama Dosen')
                    ->relationship('person', 'nama_lengkap')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn () => app(FormResolver::class)->prodiOptions(auth()->user()))
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('jenis_dosen')
                    ->label('Jenis Dosen')
                    ->options([
                        'TETAP' => 'Dosen Tetap',
                        'LB' => 'Dosen Luar Biasa',
                        'PRAKTISI' => 'Praktisi',
                        'TIDAK_TETAP' => 'Tidak Tetap',
                    ])
                    ->default('TETAP')
                    ->required(),

                TextInput::make('nidn')
                    ->label('NIDN')
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),

                TextInput::make('nuptk')
                    ->label('NUPTK')
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),

                TextInput::make('asal_institusi')
                    ->label('Asal Institusi')
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true),
            ]);
    }
}
