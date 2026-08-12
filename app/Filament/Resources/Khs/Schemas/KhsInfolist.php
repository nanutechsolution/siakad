<?php

namespace App\Filament\Resources\Khs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class KhsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | HEADER DOKUMEN
                |--------------------------------------------------------------------------
                */
                Section::make()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])
                            ->schema([

                                TextEntry::make('mahasiswa.nim')
                                    ->label('NIM')
                                    ->icon('heroicon-o-identification')
                                    ->copyable()
                                    ->weight(FontWeight::Bold)
                                    ->extraAttributes([
                                        'class' => 'rounded-xl bg-gray-50 dark:bg-white/5 px-4 py-3',
                                    ]),

                                TextEntry::make('tahunAkademik.nama_tahun')
                                    ->label('Tahun Akademik')
                                    ->icon('heroicon-o-calendar-days')
                                    ->weight(FontWeight::Bold)
                                    ->extraAttributes([
                                        'class' => 'rounded-xl bg-gray-50 dark:bg-white/5 px-4 py-3',
                                    ]),

                                TextEntry::make('mahasiswa.prodi.nama_prodi')
                                    ->label('Program Studi')
                                    ->icon('heroicon-o-academic-cap')
                                    ->badge()
                                    ->color('primary')
                                    ->extraAttributes([
                                        'class' => 'rounded-xl bg-gray-50 dark:bg-white/5 px-4 py-3',
                                    ]),
                            ]),
                    ])
                    ->compact()
                    ->extraAttributes([
                        'class' => 'border-primary-200 dark:border-primary-800',
                    ]),


                /*
                |--------------------------------------------------------------------------
                | IDENTITAS MAHASISWA
                |--------------------------------------------------------------------------
                */
                Section::make('Identitas Mahasiswa')
                    ->description('Data mahasiswa yang tercatat pada Kartu Hasil Studi.')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([

                                TextEntry::make('mahasiswa.person.nama_lengkap')
                                    ->label('Nama Lengkap')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large)
                                    ->icon('heroicon-o-user')
                                    ->extraAttributes([
                                        'class' => 'rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-4',
                                    ]),

                                TextEntry::make('mahasiswa.nim')
                                    ->label('Nomor Induk Mahasiswa')
                                    ->icon('heroicon-o-identification')
                                    ->copyable()
                                    ->weight(FontWeight::SemiBold)
                                    ->extraAttributes([
                                        'class' => 'rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-4',
                                    ]),

                                TextEntry::make('mahasiswa.prodi.nama_prodi')
                                    ->label('Program Studi')
                                    ->icon('heroicon-o-building-library')
                                    ->weight(FontWeight::SemiBold)
                                    ->extraAttributes([
                                        'class' => 'rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-4',
                                    ]),

                                TextEntry::make('tahunAkademik.nama_tahun')
                                    ->label('Tahun Akademik')
                                    ->icon('heroicon-o-calendar')
                                    ->weight(FontWeight::SemiBold)
                                    ->extraAttributes([
                                        'class' => 'rounded-xl border border-gray-200 dark:border-gray-700 px-4 py-4',
                                    ]),
                            ]),
                    ]),


                /*
                |--------------------------------------------------------------------------
                | HASIL STUDI
                |--------------------------------------------------------------------------
                */
                Section::make('Ringkasan Hasil Studi')
                    ->description('Indeks prestasi mahasiswa pada tahun akademik yang dipilih.')
                    ->icon('heroicon-o-chart-bar-square')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([

                                TextEntry::make('riwayatStatus.ips')
                                    ->label('Indeks Prestasi Semester')
                                    ->formatStateUsing(
                                        fn($state) => filled($state)
                                            ? number_format((float) $state, 2, ',', '.')
                                            : '—'
                                    )
                                    ->icon('heroicon-o-chart-bar')
                                    ->iconColor('success')
                                    ->weight(FontWeight::ExtraBold)
                                    ->size(TextSize::Large)
                                    ->color('success')
                                    ->extraAttributes([
                                        'class' => '
                                            rounded-2xl
                                            border border-success-200
                                            bg-success-50
                                            dark:border-success-800
                                            dark:bg-success-950/30
                                            px-6 py-6
                                        ',
                                    ]),

                                TextEntry::make('riwayatStatus.ipk')
                                    ->label('Indeks Prestasi Kumulatif')
                                    ->formatStateUsing(
                                        fn($state) => filled($state)
                                            ? number_format((float) $state, 2, ',', '.')
                                            : '—'
                                    )
                                    ->icon('heroicon-o-academic-cap')
                                    ->iconColor('primary')
                                    ->weight(FontWeight::ExtraBold)
                                    ->size(TextSize::Large)
                                    ->color('primary')
                                    ->extraAttributes([
                                        'class' => '
                                            rounded-2xl
                                            border border-primary-200
                                            bg-primary-50
                                            dark:border-primary-800
                                            dark:bg-primary-950/30
                                            px-6 py-6
                                        ',
                                    ]),
                            ]),
                    ]),


                /*
                |--------------------------------------------------------------------------
                | FOOTER / KETERANGAN
                |--------------------------------------------------------------------------
                */
                Section::make()
                    ->schema([
                        TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->default('Dokumen ini merupakan ringkasan hasil studi mahasiswa pada semester terkait.')
                            ->icon('heroicon-o-information-circle')
                            ->color('gray')
                            ->extraAttributes([
                                'class' => 'text-sm',
                            ]),
                    ])
                    ->compact()
                    ->extraAttributes([
                        'class' => 'bg-gray-50 dark:bg-white/5',
                    ]),
            ]);
    }
}
