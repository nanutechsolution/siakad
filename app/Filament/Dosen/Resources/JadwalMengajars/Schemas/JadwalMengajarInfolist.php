<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JadwalMengajarInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Perkuliahan')
                    ->collapsed()
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('mataKuliah.nama_mk')->label('Mata Kuliah')->weight('bold'),
                            TextEntry::make('mataKuliah.kode_mk')->label('Kode MK'),
                            TextEntry::make('kelas.nama_kelas')->label('Kelas'),
                            TextEntry::make('hari')->label('Hari'),
                            TextEntry::make('jam_mulai')->label('Jam Mulai')->time('H:i'),
                            TextEntry::make('jam_selesai')->label('Jam Selesai')->time('H:i'),
                            TextEntry::make('ruang.nama_ruang')->label('Ruang'),
                            TextEntry::make('isi_kelas')
                                ->label('Mahasiswa Terdaftar')
                                ->formatStateUsing(fn($state, $record) => $state . ' / ' . $record->kuota_kelas . ' Orang'),
                        ]),
                    ]),
            ]);
    }
}
