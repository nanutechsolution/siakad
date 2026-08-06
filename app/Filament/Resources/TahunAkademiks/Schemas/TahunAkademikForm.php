<?php

namespace App\Filament\Resources\TahunAkademiks\Schemas;

use App\Enums\TahunAkademikStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class TahunAkademikForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Tahun Akademik')
                ->columnSpanFull()
                ->tabs([

                    // ------------------------------------------------------
                    Tabs\Tab::make('Informasi Utama')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('kode_tahun')
                                    ->label('Kode Tahun')
                                    ->required()
                                    ->maxLength(5)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Cth: 20261')
                                    ->helperText('Format standar: YYYYS (tahun + kode semester).')
                                    // dikunci setelah lewat draft agar konsisten dengan histori & feeder
                                    ->disabled(fn($record) => $record && $record->status !== TahunAkademikStatus::Draft)
                                    ->dehydrated(),

                                TextInput::make('nama_tahun')
                                    ->label('Nama Tahun')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('Cth: Ganjil 2026/2027'),

                                Select::make('semester')
                                    ->label('Jenis Semester')
                                    ->required()
                                    ->native(false)
                                    ->options([1 => 'Ganjil', 2 => 'Genap', 3 => 'Pendek'])
                                    ->disabled(fn($record) => $record && $record->status !== TahunAkademikStatus::Draft),

                                TextEntry::make('status_display')
                                    ->label('Status Saat Ini')
                                    ->state(fn($record) => $record?->status?->getLabel() ?? 'Draft (belum disimpan)'),
                            ]),

                            Fieldset::make('Periode Umum Semester')
                                ->schema([
                                    DatePicker::make('tanggal_mulai')
                                        ->label('Tanggal Mulai')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('d M Y'),
                                    DatePicker::make('tanggal_selesai')
                                        ->label('Tanggal Selesai')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('d M Y')
                                        ->afterOrEqual('tanggal_mulai'),
                                ])->columns(2),

                            KeyValue::make('config')
                                ->label('Konfigurasi Tambahan')
                                ->helperText('Pengaturan bebas dalam format key-value, disimpan sebagai JSON.')
                                ->columnSpanFull(),
                        ]),

                    // ------------------------------------------------------
                    Tabs\Tab::make('KRS & Perkuliahan')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Section::make('Rencana Periode KRS')
                                ->description('Tanggal rencana. Pembukaan/penutupan aktual dijalankan lewat aksi workflow di halaman "Kelola Semester", bukan di sini.')
                                ->schema([
                                    DatePicker::make('tgl_mulai_krs')->label('Rencana Mulai KRS')->native(false),
                                    DatePicker::make('tgl_selesai_krs')->label('Rencana Selesai KRS')->native(false)
                                        ->afterOrEqual('tgl_mulai_krs'),
                                ])->columns(2),

                            Section::make('Periode Perkuliahan')
                                ->schema([
                                    DatePicker::make('tgl_mulai_perkuliahan')->label('Mulai Perkuliahan')->native(false),
                                    DatePicker::make('tgl_selesai_perkuliahan')->label('Selesai Perkuliahan')->native(false)
                                        ->afterOrEqual('tgl_mulai_perkuliahan'),
                                ])->columns(2),

                            Section::make('Ujian')
                                ->schema([
                                    DatePicker::make('tgl_mulai_uts')->label('Mulai UTS')->native(false),
                                    DatePicker::make('tgl_selesai_uts')->label('Selesai UTS')->native(false)
                                        ->afterOrEqual('tgl_mulai_uts'),
                                    DatePicker::make('tgl_mulai_uas')->label('Mulai UAS')->native(false),
                                    DatePicker::make('tgl_selesai_uas')->label('Selesai UAS')->native(false)
                                        ->afterOrEqual('tgl_mulai_uas'),
                                ])->columns(4),
                        ])
                        ->disabled(fn($record) => $record && ! in_array($record->status, [
                            TahunAkademikStatus::Draft,
                            TahunAkademikStatus::KrsBuka,
                        ])),

                    // ------------------------------------------------------
                    Tabs\Tab::make('Penilaian')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema([
                            Grid::make(2)->schema([
                                DatePicker::make('tgl_mulai_input_nilai')
                                    ->label('Rencana Mulai Input Nilai')
                                    ->native(false),
                                DatePicker::make('tgl_selesai_input_nilai')
                                    ->label('Batas Akhir Input Nilai')
                                    ->native(false)
                                    ->afterOrEqual('tgl_mulai_input_nilai'),
                            ]),

                            Placeholder::make('tgl_publish_nilai_display')
                                ->label('Tanggal Publish Nilai (KHS)')
                                ->content(fn($record) => $record?->tgl_publish_nilai?->format('d M Y')
                                    ?? 'Belum dipublish — diisi otomatis saat aksi "Publish Nilai" dijalankan.'),
                        ])
                        ->disabled(fn($record) => $record && ! in_array($record->status, [
                            TahunAkademikStatus::Draft,
                            TahunAkademikStatus::KrsBuka,
                            TahunAkademikStatus::KrsTutup,
                            TahunAkademikStatus::Perkuliahan,
                        ])),

                    // ------------------------------------------------------
                    Tabs\Tab::make('Status Sistem')
                        ->icon('heroicon-o-lock-closed')
                        ->schema([
                            Section::make('Riwayat Transisi')
                                ->description('Kolom ini tercatat otomatis oleh sistem melalui aksi workflow (Buka KRS, Publish Nilai, dst) dan tidak dapat diedit manual dari sini.')
                                ->schema([
                                    Placeholder::make('krs_dibuka_at')
                                        ->label('KRS Dibuka')
                                        ->content(fn($record) => $record?->krs_dibuka_at?->format('d M Y H:i') ?? '—'),
                                    Placeholder::make('krs_ditutup_at')
                                        ->label('KRS Ditutup')
                                        ->content(fn($record) => $record?->krs_ditutup_at?->format('d M Y H:i') ?? '—'),
                                    Placeholder::make('nilai_dikunci_at')
                                        ->label('Nilai Dikunci')
                                        ->content(fn($record) => $record?->nilai_dikunci_at?->format('d M Y H:i') ?? '—'),
                                    Placeholder::make('nilai_dipublish_at')
                                        ->label('Nilai Dipublish')
                                        ->content(fn($record) => $record?->nilai_dipublish_at?->format('d M Y H:i') ?? '—'),
                                    Placeholder::make('semester_ditutup_at')
                                        ->label('Semester Ditutup')
                                        ->content(fn($record) => $record?->semester_ditutup_at?->format('d M Y H:i') ?? '—'),
                                    Placeholder::make('ditutup_by')
                                        ->label('Ditutup Oleh')
                                        ->content(fn($record) => $record?->ditutupBy?->name ?? '—'),
                                ])->columns(3),

                            Toggle::make('is_active')
                                ->label('Tandai sebagai Semester Aktif')
                                ->helperText('Hanya satu semester yang boleh aktif pada satu waktu. Mengaktifkan semester ini tidak otomatis menonaktifkan yang lain — pastikan dikelola lewat proses aktivasi resmi.')
                                ->inline(false),
                        ]),

                    // ------------------------------------------------------
                    Tabs\Tab::make('Feeder DIKTI')
                        ->icon('heroicon-o-server')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('feeder_semester_id')
                                    ->label('ID Semester Feeder')
                                    ->maxLength(255),

                                Toggle::make('is_feeder_locked')
                                    ->label('Kunci Sinkronisasi Feeder')
                                    ->helperText('Saat dikunci, sinkronisasi otomatis ke Feeder DIKTI dihentikan sementara.')
                                    ->inline(false),
                            ]),

                            TextEntry::make('last_sync_at_display')
                                ->label('Sinkronisasi Terakhir')
                                ->state(fn($record) => $record?->last_sync_at?->format('d M Y H:i') ?? 'Belum pernah sinkronisasi'),
                        ]),
                ]),
        ]);
    }
}
