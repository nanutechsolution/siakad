<?php

namespace App\Filament\Resources\RefPeople\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class RefPersonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | PROFIL UTAMA
                |--------------------------------------------------------------------------
                */

                Section::make()
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 3,
                            'lg' => 4,
                        ])
                            ->schema([

                                /*
                                | FOTO
                                */

                                ImageEntry::make('photo_path')
                                    ->hiddenLabel()
                                    ->circular()
                                    ->imageSize(150)
                                    ->defaultImageUrl(
                                        url('/images/default-avatar.png')
                                    )
                                    ->extraImgAttributes([
                                        'alt' => 'Foto profil',
                                        'loading' => 'lazy',
                                    ])
                                    ->columnSpan([
                                        'default' => 1,
                                        'sm' => 1,
                                        'lg' => 1,
                                    ]),

                                /*
                                | IDENTITAS UTAMA
                                */

                                Grid::make(1)
                                    ->schema([

                                        TextEntry::make('nama_lengkap')
                                            ->hiddenLabel()
                                            ->size(TextSize::Large)
                                            ->weight('bold')
                                            ->placeholder('Nama belum tersedia'),

                                        TextEntry::make('nik')
                                            ->label('NIK')
                                            ->icon('heroicon-o-identification')
                                            ->copyable()
                                            ->copyMessage('NIK berhasil disalin')
                                            ->copyMessageDuration(1500)
                                            ->placeholder('Belum tersedia'),

                                        TextEntry::make('jenis_kelamin')
                                            ->label('Jenis Kelamin')
                                            ->badge()
                                            ->formatStateUsing(
                                                fn(?string $state): string => match ($state) {
                                                    'L' => 'Laki-laki',
                                                    'P' => 'Perempuan',
                                                    default => 'Belum diisi',
                                                }
                                            )
                                            ->color(
                                                fn(?string $state): string => match ($state) {
                                                    'L' => 'info',
                                                    'P' => 'success',
                                                    default => 'gray',
                                                }
                                            ),

                                    ])
                                    ->columnSpan([
                                        'default' => 1,
                                        'sm' => 2,
                                        'lg' => 3,
                                    ]),

                            ]),

                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | IDENTITAS PRIBADI
                |--------------------------------------------------------------------------
                */

                Section::make('Identitas Pribadi')
                    ->description(
                        'Informasi identitas dasar yang tersimpan pada master Person.'
                    )
                    ->icon('heroicon-o-identification')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 4,
                        ])
                            ->schema([

                                TextEntry::make('nik')
                                    ->label('NIK / No. KTP')
                                    ->icon('heroicon-o-identification')
                                    ->copyable()
                                    ->copyMessage('NIK berhasil disalin')
                                    ->copyMessageDuration(1500)
                                    ->placeholder('Belum diisi'),

                                TextEntry::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->badge()
                                    ->formatStateUsing(
                                        fn(?string $state): string => match ($state) {
                                            'L' => 'Laki-laki',
                                            'P' => 'Perempuan',
                                            default => 'Belum diisi',
                                        }
                                    )
                                    ->color(
                                        fn(?string $state): string => match ($state) {
                                            'L' => 'info',
                                            'P' => 'success',
                                            default => 'gray',
                                        }
                                    ),

                                TextEntry::make('tempat_lahir')
                                    ->label('Tempat Lahir')
                                    ->icon('heroicon-o-map-pin')
                                    ->placeholder('Belum diisi'),

                                TextEntry::make('tanggal_lahir')
                                    ->label('Tanggal Lahir')
                                    ->icon('heroicon-o-calendar-days')
                                    ->date('d F Y')
                                    ->placeholder('Belum diisi'),

                            ]),

                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | KONTAK
                |--------------------------------------------------------------------------
                */

                Section::make('Informasi Kontak')
                    ->description(
                        'Kontak pribadi yang dapat digunakan untuk komunikasi.'
                    )
                    ->icon('heroicon-o-phone')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([

                                TextEntry::make('email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope')
                                    ->placeholder('Belum diisi')
                                    ->copyable()
                                    ->copyMessage('Email berhasil disalin')
                                    ->copyMessageDuration(1500)
                                    ->url(
                                        fn(?string $state): ?string =>
                                        filled($state)
                                            ? "mailto:{$state}"
                                            : null
                                    ),

                                TextEntry::make('no_hp')
                                    ->label('No. HP / WhatsApp')
                                    ->icon('heroicon-o-phone')
                                    ->placeholder('Belum diisi')
                                    ->copyable()
                                    ->copyMessage('Nomor HP berhasil disalin')
                                    ->copyMessageDuration(1500)
                                    ->url(
                                        fn(?string $state): ?string =>
                                        filled($state)
                                            ? "tel:{$state}"
                                            : null
                                    ),

                            ]),

                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | INFORMASI KEPEGAWAIAN
                |--------------------------------------------------------------------------
                |
                | Jika Person mempunyai relationship pegawai().
                |
                */

                Section::make('Informasi Kepegawaian')
                    ->description(
                        'Status Person dalam sistem kepegawaian.'
                    )
                    ->icon('heroicon-o-briefcase')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([

                                TextEntry::make('pegawai.nip')
                                    ->label('NIP')
                                    ->icon('heroicon-o-identification')
                                    ->placeholder('Belum memiliki NIP')
                                    ->copyable()
                                    ->copyMessage('NIP berhasil disalin')
                                    ->copyMessageDuration(1500),

                                TextEntry::make('pegawai.jenis_pegawai')
                                    ->label('Jenis Pegawai')
                                    ->badge()
                                    ->placeholder('Belum terdaftar'),

                                TextEntry::make('pegawai.is_active')
                                    ->label('Status Kepegawaian')
                                    ->badge()
                                    ->formatStateUsing(
                                        fn($state): string =>
                                        $state
                                            ? 'Aktif'
                                            : 'Tidak Aktif'
                                    )
                                    ->color(
                                        fn($state): string =>
                                        $state
                                            ? 'success'
                                            : 'danger'
                                    )
                                    ->placeholder('Belum terdaftar'),

                            ]),

                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | AUDIT / INFORMASI SISTEM
                |--------------------------------------------------------------------------
                */

                Section::make('Informasi Sistem')
                    ->description(
                        'Informasi pencatatan dan perubahan data.'
                    )
                    ->icon('heroicon-o-information-circle')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([

                                TextEntry::make('created_at')
                                    ->label('Terdaftar')
                                    ->icon('heroicon-o-calendar-days')
                                    ->dateTime('d F Y, H:i')
                                    ->placeholder('-'),

                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->icon('heroicon-o-clock')
                                    ->dateTime('d F Y, H:i')
                                    ->placeholder('-'),

                            ]),

                    ])
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->id('ref-person-system-information')
                    ->columnSpanFull(),

            ]);
    }
}
