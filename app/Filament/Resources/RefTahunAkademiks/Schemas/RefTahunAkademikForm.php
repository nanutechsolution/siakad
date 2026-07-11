<?php

namespace App\Filament\Resources\RefTahunAkademiks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class RefTahunAkademikForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tahun Akademik Info')
                    ->tabs([
                        // TAB 1: INFORMASI UMUM
                        Tabs\Tab::make('Informasi Utama')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('kode_tahun')
                                            ->label('Kode Tahun')
                                            ->required()
                                            ->maxLength(5)
                                            ->unique()
                                            ->placeholder('Cth: 20231'),

                                        TextInput::make('nama_tahun')
                                            ->label('Nama Tahun')
                                            ->required()
                                            ->maxLength(50)
                                            ->placeholder('Cth: Ganjil 2023/2024'),

                                        Select::make('semester')
                                            ->label('Semester')
                                            ->required()
                                            ->options([
                                                1 => 'Ganjil',
                                                2 => 'Genap',
                                                3 => 'Pendek',
                                            ]),

                                        KeyValue::make('config')
                                            ->label('Konfigurasi Tambahan (JSON)')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // TAB 2: JADWAL & TANGGAL
                        Tabs\Tab::make('Jadwal & Tanggal')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([
                                Fieldset::make('Periode Umum')
                                    ->schema([
                                        DatePicker::make('tanggal_mulai')
                                            ->native(false)->displayFormat('d M Y'),
                                        DatePicker::make('tanggal_selesai')
                                            ->native(false)->displayFormat('d M Y'),
                                    ]),

                                Fieldset::make('KRS & Perkuliahan')
                                    ->schema([
                                        DatePicker::make('tgl_mulai_krs')
                                            ->label('Mulai KRS')->native(false),
                                        DatePicker::make('tgl_selesai_krs')
                                            ->label('Selesai KRS')->native(false),
                                        DatePicker::make('tgl_mulai_perkuliahan')
                                            ->label('Mulai Perkuliahan')->native(false),
                                        DatePicker::make('tgl_selesai_perkuliahan')
                                            ->label('Selesai Perkuliahan')->native(false),
                                    ])->columns(4),

                                Fieldset::make('Ujian (UTS & UAS)')
                                    ->schema([
                                        DatePicker::make('tgl_mulai_uts')
                                            ->label('Mulai UTS')->native(false),
                                        DatePicker::make('tgl_selesai_uts')
                                            ->label('Selesai UTS')->native(false),
                                        DatePicker::make('tgl_mulai_uas')
                                            ->label('Mulai UAS')->native(false),
                                        DatePicker::make('tgl_selesai_uas')
                                            ->label('Selesai UAS')->native(false),
                                    ])->columns(4),

                                Fieldset::make('Penilaian')
                                    ->schema([
                                        DatePicker::make('tgl_mulai_input_nilai')
                                            ->label('Mulai Input Nilai')->native(false),
                                        DatePicker::make('tgl_selesai_input_nilai')
                                            ->label('Batas Input Nilai')->native(false),
                                        DatePicker::make('tgl_publish_nilai')
                                            ->label('Publish Nilai (KHS)')->native(false),
                                    ])->columns(3),
                            ]),

                        // TAB 3: KONTROL SISTEM
                        Tabs\Tab::make('Status & Akses')
                            ->icon('heroicon-o-lock-closed')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Tahun Akademik Aktif')
                                            ->inline(false)
                                            ->default(false),

                                        Toggle::make('buka_krs')
                                            ->label('Buka KRS')
                                            ->inline(false)
                                            ->default(false),

                                        Toggle::make('is_locked_krs')
                                            ->label('Lock Manual KRS')
                                            ->inline(false)
                                            ->default(false),

                                        Toggle::make('buka_input_nilai')
                                            ->label('Buka Input Nilai')
                                            ->inline(false)
                                            ->default(false),

                                        Toggle::make('is_locked_nilai')
                                            ->label('Lock Manual Input Nilai')
                                            ->inline(false)
                                            ->default(false),
                                    ]),
                            ]),

                        // TAB 4: INTEGRASI FEEDER
                        Tabs\Tab::make('Feeder DIKTI')
                            ->icon('heroicon-o-server')
                            ->schema([
                                TextInput::make('feeder_semester_id')
                                    ->label('ID Semester Feeder')
                                    ->maxLength(255),

                                Toggle::make('is_feeder_locked')
                                    ->label('Lock Sinkronisasi Feeder')
                                    ->inline(false)
                                    ->default(false),
                                DateTimePicker::make('last_sync_at')
                                    ->label('Sinkronisasi Terakhir')
                                    ->native(false)
                                    ->disabled(),
                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }
}
