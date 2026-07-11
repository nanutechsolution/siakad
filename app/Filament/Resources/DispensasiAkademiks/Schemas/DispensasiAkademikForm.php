<?php

namespace App\Filament\Resources\DispensasiAkademiks\Schemas;

use App\Models\DispensasiAkademik;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DispensasiAkademikForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Mahasiswa & Dispensasi')
                    ->schema([
                        Select::make('mahasiswa_id')
                            ->label('Mahasiswa')
                            ->relationship('mahasiswa', 'nim')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nim} - {$record->person->nama_lengkap}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(request()->query('mahasiswa_id')) // Mengambil ID dari URL shortcut KRS
                            ->disabled(fn(string $operation) => $operation !== 'create'),

                        Select::make('jenis')
                            ->label('Jenis Dispensasi')
                            ->options([
                                'KRS' => 'Dispensasi Pengisian KRS',
                            ])
                            ->default('KRS')
                            ->required()
                            ->disabled(fn(string $operation) => $operation !== 'create'),

                        Grid::make(2)->schema([
                            DatePicker::make('berlaku_mulai')
                                ->label('Berlaku Mulai')
                                ->required(),

                            DatePicker::make('berlaku_sampai')
                                ->label('Berlaku Sampai')
                                ->required()
                                ->afterOrEqual('berlaku_mulai'),
                        ]),

                        Textarea::make('alasan')
                            ->label('Alasan Dispensasi')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->disabled(fn(?DispensasiAkademik $record) => $record && $record->status !== 'DRAFT'), // Kunci form jika bukan DRAFT
            ]);
    }
}
