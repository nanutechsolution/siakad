<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JadwalGeneratorBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Target Penjadwalan')
                    ->schema([
                        Select::make('tahun_akademik_id')
                            ->relationship('tahunAkademik', 'nama_tahun')
                            ->default(fn() => \App\Models\RefTahunAkademik::where('is_active', 1)->value('id'))
                            ->required(),

                        Select::make('prodi_id')
                            ->relationship('prodi', 'nama_prodi')
                            ->required(),
                    ])->columns(2),

                Section::make('Konfigurasi Waktu (Termasuk Istirahat)')
                    ->description('Tentukan hari aktif dan blok waktu perkuliahan. Jangan masukkan rentang waktu istirahat (misal 12:30 - 13:00) ke dalam slot agar mesin tidak menjadwalkan kelas di jam tersebut.')
                    ->schema([
                        CheckboxList::make('config_snapshot.hari')
                            ->label('Hari Operasional')
                            ->options([
                                'Senin' => 'Senin',
                                'Selasa' => 'Selasa',
                                'Rabu' => 'Rabu',
                                'Kamis' => 'Kamis',
                                'Jumat' => 'Jumat',
                                'Sabtu' => 'Sabtu'
                            ])
                            ->default(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])
                            ->columns(3)
                            ->required(),

                        Repeater::make('config_snapshot.slots')
                            ->label('Blok Waktu Perkuliahan')
                            ->schema([
                                TimePicker::make('mulai')
                                    ->label('Jam Mulai')
                                    ->seconds(false)
                                    ->required(),
                                TimePicker::make('selesai')
                                    ->label('Jam Selesai')
                                    ->seconds(false)
                                    ->required(),
                            ])
                            ->default([
                                ['mulai' => '08:00', 'selesai' => '09:30'],
                                ['mulai' => '09:30', 'selesai' => '11:00'],
                                ['mulai' => '11:00', 'selesai' => '12:30'],
                                ['mulai' => '13:00', 'selesai' => '14:30'],
                                ['mulai' => '14:30', 'selesai' => '16:00'],
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->reorderable()
                            ->required(),
                    ]),
            ]);
    }
}
