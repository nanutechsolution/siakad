<?php

namespace App\Filament\Resources\Khs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;

class KhsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kartu Hasil Studi')
                    ->description('Rangkuman data akademik mahasiswa pada semester terkait.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        // ==============================
                        // KIRI - IDENTITAS MAHASISWA
                        // ==============================
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('mahasiswa.nim')
                                    ->label('NIM')
                                    ->icon('heroicon-m-identification')
                                    ->copyable(),

                                TextEntry::make('mahasiswa.person.nama_lengkap')
                                    ->label('Nama Lengkap')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large),

                                TextEntry::make('mahasiswa.prodi.nama_prodi')
                                    ->label('Program Studi')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('tahunAkademik.nama_tahun')
                                    ->label('Tahun Akademik')
                                    ->icon('heroicon-m-calendar'),
                            ]),

                        // ==============================
                        // KANAN - NILAI IPS & IPK
                        // ==============================
                        Grid::make(1)
                            ->schema([
                                TextEntry::make('riwayatStatus.ips')
                                    ->label('Indeks Prestasi Semester (IPS)')
                                    ->weight(FontWeight::ExtraBold)
                                    ->color('success')
                                    ->size(TextSize::Large),

                                TextEntry::make('riwayatStatus.ipk')
                                    ->label('Indeks Prestasi Kumulatif (IPK)')
                                    ->weight(FontWeight::Bold),
                            ])
                            ->grow(false),
                    ]),
            ]);
    }
}
