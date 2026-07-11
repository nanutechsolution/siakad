<?php

namespace App\Filament\Resources\Mahasiswas\Schemas;

use App\Filament\Resources\RefPeople\RefPersonResource;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class MahasiswaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

            Tabs::make('LabelTabbing')
                    ->columnSpanFull()
                    ->tabs([
                        
                        // TAB 1: PROFIL AKADEMIK UTAMA
                        Tab::make('Profil Akademik')
                            ->icon('heroicon-m-academic-cap')
                            ->schema([
                                Grid::make(['default' => 1, 'md' => 4])
                                    ->schema([
                                        ImageEntry::make('person.photo_path')
                                            ->hiddenLabel()
                                            ->circular()
                                            ->defaultImageUrl(url('https://ui-avatars.com/api/?name=M&color=7F9CF5&background=EBF4FF'))
                                            ->size(150)
                                            ->columnSpan(1),

                                        Grid::make(2)
                                            ->columnSpan(['default' => 1, 'md' => 3])
                                            ->schema([
                                                TextEntry::make('person.nama_lengkap')
                                                    ->label('Nama Lengkap')
                                                    ->size(TextSize::Large)
                                                    ->weight(FontWeight::Bold)
                                                    ->columnSpanFull()
                                                    ->url(fn($record) => RefPersonResource::getUrl('view', ['record' => $record->person_id])),

                                                TextEntry::make('nim')
                                                    ->label('Nomor Induk Mahasiswa (NIM)')
                                                    ->badge()
                                                    ->color('primary')
                                                    ->copyable(),

                                                TextEntry::make('prodi.nama_prodi')
                                                    ->label('Program Studi')
                                                    ->weight(FontWeight::SemiBold),

                                                TextEntry::make('angkatan.id_tahun')
                                                    ->label('Angkatan')
                                                    ->badge()
                                                    ->color('gray'),

                                                TextEntry::make('program.nama_program')
                                                    ->label('Program Kelas')
                                                    ->default('-'),

                                                TextEntry::make('kurikulum.nama_kurikulum')
                                                    ->label('Kurikulum Aktif')
                                                    ->default('-'),
                                            ]),
                                    ]),
                            ]),

                        // TAB 2: BIODATA DIRI
                        Tab::make('Biodata Diri')
                            ->icon('heroicon-m-user-circle')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('person.nik')
                                            ->label('Nomor Induk Kependudukan (NIK)')
                                            ->copyable(),
                                            
                                        TextEntry::make('person.jenis_kelamin')
                                            ->label('Jenis Kelamin')
                                            ->formatStateUsing(fn (string $state): string => $state === 'L' ? 'Laki-laki' : ($state === 'P' ? 'Perempuan' : $state)),
                                            
                                        TextEntry::make('person.agama')
                                            ->label('Agama'),

                                        TextEntry::make('person.tempat_lahir')
                                            ->label('Tempat Lahir'),

                                        TextEntry::make('person.tanggal_lahir')
                                            ->label('Tanggal Lahir')
                                            ->date('d F Y'),

                                        TextEntry::make('person.no_hp')
                                            ->label('No. Handphone / WhatsApp')
                                            ->copyable(),

                                        TextEntry::make('person.alamat_lengkap')
                                            ->label('Alamat Domisili')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // TAB 3: RIWAYAT PERKULIAHAN & IPK
                        Tab::make('Riwayat Studi & IPK')
                            ->icon('heroicon-m-chart-bar')
                            ->schema([
                                RepeatableEntry::make('riwayatStatuses')
                                    ->label('Riwayat Status') 
                                    ->schema([
                                        Grid::make(5)
                                            ->schema([
                                                TextEntry::make('tahunAkademik.nama_tahun')
                                                    ->label('Periode Semester')
                                                    ->weight(FontWeight::Bold),
                                                    
                                                TextEntry::make('status_kuliah')
                                                    ->label('Status')
                                                    ->badge()
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'A' => 'success',
                                                        'C' => 'warning',
                                                        'D' => 'danger',
                                                        'L' => 'info',
                                                        default => 'gray',
                                                    }),

                                                TextEntry::make('sks_semester')
                                                    ->label('SKS Diambil')
                                                    ->numeric(),

                                                TextEntry::make('ips')
                                                    ->label('IPS'),

                                                TextEntry::make('ipk')
                                                    ->label('IPK')
                                                    ->badge()
                                                    ->color('success'),
                                            ])
                                    ])
                                    ->emptyTooltip('Belum ada riwayat studi')
                            ]),

                        // TAB 4: SISTEM & INTEGRASI FEEDER
                        Tab::make('Data Sistem & Feeder')
                            ->icon('heroicon-m-server')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('id_pd_feeder')
                                            ->label('ID Neo Feeder (DIKTI)')
                                            ->copyable()
                                            ->placeholder('Belum tersinkronisasi')
                                            ->color(fn ($state) => blank($state) ? 'danger' : 'gray'),
                                            
                                        TextEntry::make('last_synced_at')
                                            ->label('Terakhir Sinkronisasi')
                                            ->dateTime('d M Y, H:i')
                                            // Perbaikan: Menggunakan placeholder alih-alih default
                                            ->placeholder('Belum pernah disinkronkan'),
                                            
                                        // TextEntry::make('data_tambahan')
                                        //     ->label('Metadata Tambahan')
                                        //     // Perbaikan: Penanganan array bawaan Filament
                                        //     ->listWithLineBreaks()
                                        //     ->bulleted()
                                        //     ->placeholder('Tidak ada data tambahan')
                                        //     ->columnSpanFull(),
                                    ])
                            ]),
                            
                    ]),
               ]);
    }
}
