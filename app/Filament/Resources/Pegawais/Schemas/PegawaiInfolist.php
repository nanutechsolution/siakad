<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PegawaiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /*
                |--------------------------------------------------------------------------
                | PROFIL / IDENTITAS UTAMA
                |--------------------------------------------------------------------------
                */

                Section::make('Profil Pegawai')
                    ->description(
                        'Informasi utama pegawai yang terdaftar dalam sistem.'
                    )
                    ->icon('heroicon-o-user-circle')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([

                                TextEntry::make('person.nama_lengkap')
                                    ->label('Nama Lengkap')
                                    ->formatStateUsing(
                                        fn($record): string =>
                                        $record->person?->nama_dengan_gelar
                                            ?? $record->person?->nama_lengkap
                                            ?? '-'
                                    )
                                    ->weight('bold')
                                    ->size('lg')
                                    ->columnSpan([
                                        'default' => 1,
                                        'sm' => 2,
                                        'lg' => 2,
                                    ]),

                                IconEntry::make('is_active')
                                    ->label('Status')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),

                            ]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | DATA KEPEGAWAIAN
                |--------------------------------------------------------------------------
                */

                Section::make('Data Kepegawaian')
                    ->description(
                        'Informasi status dan identitas kepegawaian.'
                    )
                    ->icon('heroicon-o-briefcase')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 3,
                        ])
                            ->schema([

                                TextEntry::make('nip')
                                    ->label('NIP')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->copyMessage('NIP berhasil disalin')
                                    ->copyMessageDuration(1500),

                                TextEntry::make('jenis_pegawai')
                                    ->label('Jenis Pegawai')
                                    ->badge(),

                                TextEntry::make('created_at')
                                    ->label('Tanggal Registrasi')
                                    ->date('d F Y')
                                    ->placeholder('-'),

                            ]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | DATA IDENTITAS
                |--------------------------------------------------------------------------
                */

                Section::make('Data Identitas')
                    ->description(
                        'Informasi identitas pribadi pegawai.'
                    )
                    ->icon('heroicon-o-identification')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([

                                TextEntry::make('person.nik')
                                    ->label('NIK')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->copyMessage('NIK berhasil disalin')
                                    ->copyMessageDuration(1500),

                                TextEntry::make('person.jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->formatStateUsing(
                                        fn(?string $state): string => match ($state) {
                                            'L' => 'Laki-laki',
                                            'P' => 'Perempuan',
                                            default => '-',
                                        }
                                    )
                                    ->badge()
                                    ->color(
                                        fn(?string $state): string => match ($state) {
                                            'L' => 'info',
                                            'P' => 'danger',
                                            default => 'gray',
                                        }
                                    ),

                                TextEntry::make('person.tempat_lahir')
                                    ->label('Tempat Lahir')
                                    ->placeholder('-'),

                                TextEntry::make('person.tanggal_lahir')
                                    ->label('Tanggal Lahir')
                                    ->date('d F Y')
                                    ->placeholder('-'),

                            ]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | KONTAK
                |--------------------------------------------------------------------------
                */

                Section::make('Kontak')
                    ->description(
                        'Informasi kontak yang dapat digunakan untuk menghubungi pegawai.'
                    )
                    ->icon('heroicon-o-phone')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([

                                TextEntry::make('person.email')
                                    ->label('Email')
                                    ->icon('heroicon-o-envelope')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->copyMessage('Email berhasil disalin')
                                    ->copyMessageDuration(1500)
                                    ->url(
                                        fn($state) => filled($state)
                                            ? "mailto:{$state}"
                                            : null
                                    ),

                                TextEntry::make('person.no_hp')
                                    ->label('Nomor Handphone')
                                    ->icon('heroicon-o-phone')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->copyMessage('Nomor HP berhasil disalin')
                                    ->copyMessageDuration(1500)
                                    ->url(
                                        fn($state) => filled($state)
                                            ? "tel:{$state}"
                                            : null
                                    ),

                            ]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | INFORMASI SISTEM
                |--------------------------------------------------------------------------
                */

                Section::make('Informasi Sistem')
                    ->description(
                        'Informasi teknis mengenai data pegawai.'
                    )
                    ->icon('heroicon-o-information-circle')
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                        ])
                            ->schema([

                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime('d F Y H:i')
                                    ->placeholder('-'),

                                TextEntry::make('updated_at')
                                    ->label('Terakhir Diperbarui')
                                    ->dateTime('d F Y H:i')
                                    ->placeholder('-'),

                            ]),

                    ])
                    ->collapsible()
                    ->collapsed(),

            ]);
    }
}
