<?php

namespace App\Filament\Resources\LpmAmiPrograms\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class LpmAmiProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('periode_id')
                    ->label('Periode Audit')
                    ->relationship('periode', 'nama_periode')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('unit_kerja_id')
                    ->label('Unit yang Diaudit')
                    ->relationship('unitKerja', 'nama_unit')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('tanggal_pelaksanaan')
                    ->label('Tanggal Pelaksanaan'),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'DIJADWALKAN' => 'Dijadwalkan',
                        'BERLANGSUNG' => 'Berlangsung',
                        'SELESAI' => 'Selesai',
                    ])
                    ->default('DIJADWALKAN')
                    ->required(),
            ]);
    }
}
