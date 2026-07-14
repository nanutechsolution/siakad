<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Schemas;

use App\Models\JadwalKuliah;
use App\Models\TrxDosen;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JadwalMengajarInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Jadwal')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('mataKuliah.nama_mk')->label('Mata Kuliah'),
                        TextEntry::make('mataKuliah.kode_mk')->label('Kode MK'),
                        TextEntry::make('kelas.nama_kelas')->label('Kelas'),
                        TextEntry::make('tahunAkademik.nama_tahun')->label('Tahun Akademik'),
                        TextEntry::make('hari')->label('Hari'),
                        TextEntry::make('jam_mulai')->label('Jam Mulai')->time('H:i'),
                        TextEntry::make('jam_selesai')->label('Jam Selesai')->time('H:i'),
                        TextEntry::make('ruang.nama_ruang')->label('Ruang')->placeholder('Belum ditentukan'),
                    ]),

                Section::make('Tim Pengajar')
                    ->schema([
                        TextEntry::make('dosenPengampu')
                            ->label('')
                            ->state(
                                fn(JadwalKuliah $record) => $record->dosenPengampu()
                                    ->with('person')
                                    ->get()
                                    ->map(
                                        fn(TrxDosen $d) => ($d->person?->nama_lengkap ?? '-') .
                                            ($d->pivot?->is_koordinator ? ' (Koordinator)' : '')
                                    )
                                    ->implode("\n")
                            )
                            ->columnSpanFull()
                            ->wrap(),
                    ]),
            ]);
    }
}
