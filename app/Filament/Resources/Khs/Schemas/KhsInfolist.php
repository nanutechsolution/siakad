<?php

namespace App\Filament\Resources\Khs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Split;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\HtmlString;

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

                                Group::make([
                                    TextEntry::make('mahasiswa.person.nama_lengkap')
                                        ->hiddenLabel()
                                        ->weight(FontWeight::ExtraBold)
                                        ->size(TextSize::Large),

                                    TextEntry::make('mahasiswa.nim')
                                        ->hiddenLabel()
                                        ->formatStateUsing(
                                            fn($state) => "NIM: {$state}"
                                        )
                                        ->color('gray'),
                                ])
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 2,
                                    ]),

                                TextEntry::make('tahunAkademik.nama_tahun')
                                    ->label('TAHUN AKADEMIK')
                                    ->badge()
                                    ->color('primary')
                                    ->size(TextSize::Large)
                                    ->alignEnd(),
                            ]),
                    ])
                    ->compact()
                    ->extraAttributes([
                        'class' => 'border-0 bg-gradient-to-r from-primary-600 to-primary-500 text-white',
                    ]),

                /*
                |--------------------------------------------------------------------------
                | IDENTITAS AKADEMIK
                |--------------------------------------------------------------------------
                */

                Section::make('Identitas Akademik')
                    ->description('Informasi mahasiswa dan periode studi yang tercatat pada Kartu Hasil Studi.')
                    ->icon('heroicon-o-identification')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 4,
                        ])
                            ->schema([

                                TextEntry::make('mahasiswa.nim')
                                    ->label('Nomor Induk Mahasiswa')
                                    ->icon('heroicon-m-identification')
                                    ->weight(FontWeight::Bold)
                                    ->copyable(),

                                TextEntry::make('mahasiswa.person.nama_lengkap')
                                    ->label('Nama Mahasiswa')
                                    ->weight(FontWeight::Bold),

                                TextEntry::make('mahasiswa.prodi.nama_prodi')
                                    ->label('Program Studi')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('tahunAkademik.nama_tahun')
                                    ->label('Tahun Akademik')
                                    ->icon('heroicon-m-calendar')
                                    ->weight(FontWeight::Bold),
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | NILAI IPS / IPK
                |--------------------------------------------------------------------------
                */

                Grid::make([
                    'default' => 1,
                    'md' => 2,
                ])
                    ->schema([

                        Section::make()
                            ->schema([

                                TextEntry::make('riwayatStatus.ips')
                                    ->label('Indeks Prestasi Semester')
                                    ->formatStateUsing(
                                        fn($state) =>
                                        filled($state)
                                            ? number_format((float) $state, 2)
                                            : '—'
                                    )
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::ExtraBold)
                                    ->color('success')
                                    ->alignCenter(),

                                TextEntry::make('riwayatStatus.ips')
                                    ->hiddenLabel()
                                    ->formatStateUsing(
                                        fn($state) =>
                                        filled($state)
                                            ? 'IPS Semester'
                                            : 'Belum tersedia'
                                    )
                                    ->color('gray')
                                    ->alignCenter(),
                            ])
                            ->icon('heroicon-o-chart-bar')
                            ->iconColor('success')
                            ->extraAttributes([
                                'class' => 'text-center border border-success-200 dark:border-success-800 bg-success-50/50 dark:bg-success-950/20',
                            ]),

                        Section::make()
                            ->schema([

                                TextEntry::make('riwayatStatus.ipk')
                                    ->label('Indeks Prestasi Kumulatif')
                                    ->formatStateUsing(
                                        fn($state) =>
                                        filled($state)
                                            ? number_format((float) $state, 2)
                                            : '—'
                                    )
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::ExtraBold)
                                    ->color('primary')
                                    ->alignCenter(),

                                TextEntry::make('riwayatStatus.ipk')
                                    ->hiddenLabel()
                                    ->formatStateUsing(
                                        fn($state) =>
                                        filled($state)
                                            ? 'IPK Kumulatif'
                                            : 'Belum tersedia'
                                    )
                                    ->color('gray')
                                    ->alignCenter(),
                            ])
                            ->icon('heroicon-o-academic-cap')
                            ->iconColor('primary')
                            ->extraAttributes([
                                'class' => 'text-center border border-primary-200 dark:border-primary-800 bg-primary-50/50 dark:bg-primary-950/20',
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | STATUS AKADEMIK
                |--------------------------------------------------------------------------
                */

                Section::make('Status Akademik')
                    ->icon('heroicon-o-check-badge')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 3,
                        ])
                            ->schema([

                                TextEntry::make('riwayatStatus.ips')
                                    ->label('IPS Semester')
                                    ->formatStateUsing(
                                        fn($state) =>
                                        filled($state)
                                            ? number_format((float) $state, 2)
                                            : 'Belum tersedia'
                                    )
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('riwayatStatus.ipk')
                                    ->label('IPK Kumulatif')
                                    ->formatStateUsing(
                                        fn($state) =>
                                        filled($state)
                                            ? number_format((float) $state, 2)
                                            : 'Belum tersedia'
                                    )
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('tahunAkademik.nama_tahun')
                                    ->label('Periode')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | FOOTER INFORMASI
                |--------------------------------------------------------------------------
                */

                Section::make()
                    ->schema([
                        TextEntry::make('informasi_khs')
                            ->hiddenLabel()
                            ->state(
                                new HtmlString(
                                    '<div class="flex items-start gap-3">
                                        <div class="shrink-0">
                                            <svg class="h-5 w-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
                                            </svg>
                                        </div>

                                        <div>
                                            <div class="font-semibold text-gray-900 dark:text-white">
                                                Kartu Hasil Studi
                                            </div>

                                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                Dokumen ini memuat ringkasan hasil studi mahasiswa
                                                pada periode akademik yang dipilih.
                                                Gunakan tombol <strong>Cetak KHS</strong>
                                                untuk menghasilkan dokumen resmi.
                                            </div>
                                        </div>
                                    </div>'
                                )
                            ),
                    ])
                    ->compact()
                    ->columnSpanFull(),
            ]);
    }
}
