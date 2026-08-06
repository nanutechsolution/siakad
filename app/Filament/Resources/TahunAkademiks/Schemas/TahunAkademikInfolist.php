<?php

namespace App\Filament\Resources\TahunAkademiks\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class TahunAkademikInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Detail Semester')
                ->columnSpanFull()
                ->tabs([
                    Tabs\Tab::make('Jadwal')
                        ->icon('heroicon-o-calendar-days')
                        ->schema([
                            Section::make('KRS & Perkuliahan')->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('tgl_mulai_krs')->label('Mulai KRS')->date('d M Y')->placeholder('—'),
                                    TextEntry::make('tgl_selesai_krs')->label('Selesai KRS')->date('d M Y')->placeholder('—'),
                                    TextEntry::make('tgl_mulai_perkuliahan')->label('Mulai Kuliah')->date('d M Y')->placeholder('—'),
                                    TextEntry::make('tgl_selesai_perkuliahan')->label('Selesai Kuliah')->date('d M Y')->placeholder('—'),
                                ]),
                            ]),
                            Section::make('Ujian & Penilaian')->schema([
                                Grid::make(3)->schema([
                                    TextEntry::make('tgl_mulai_uts')->label('Mulai UTS')->date('d M Y')->placeholder('—'),
                                    TextEntry::make('tgl_mulai_uas')->label('Mulai UAS')->date('d M Y')->placeholder('—'),
                                    TextEntry::make('tgl_publish_nilai')->label('Publish Nilai')->date('d M Y')->placeholder('Belum dipublish'),
                                ]),
                            ]),
                        ]),

                    Tabs\Tab::make('Feeder DIKTI')
                        ->icon('heroicon-o-server')
                        ->schema([
                            Grid::make(3)->schema([
                                TextEntry::make('feeder_semester_id')->label('ID Semester Feeder')->placeholder('—'),
                                IconEntry::make('is_feeder_locked')->label('Terkunci')->boolean(),
                                TextEntry::make('last_sync_at')->label('Sinkronisasi Terakhir')->dateTime('d M Y H:i')->placeholder('Belum pernah'),
                            ]),
                        ]),

                    Tabs\Tab::make('Audit Log')
                        ->icon('heroicon-o-clock')
                        ->schema([
                            RepeatableEntry::make('activities')
                                ->label('')
                                ->getStateUsing(fn($record) => method_exists($record, 'activities')
                                    ? $record->activities()->latest()->limit(20)->get()
                                    : [])
                                ->schema([
                                    TextEntry::make('description')->label('Aktivitas'),
                                    TextEntry::make('causer.name')->label('Oleh')->placeholder('Sistem'),
                                    TextEntry::make('created_at')->label('Waktu')->dateTime('d M Y H:i'),
                                ])
                                ->columns(3),
                        ]),

                    Tabs\Tab::make('Metadata')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Grid::make(3)->schema([
                                TextEntry::make('createdBy.name')->label('Dibuat Oleh')->placeholder('—'),
                                TextEntry::make('created_at')->label('Dibuat Pada')->dateTime('d M Y H:i'),
                                TextEntry::make('updatedBy.name')->label('Terakhir Diubah Oleh')->placeholder('—'),
                            ]),
                        ]),
                ]),
        ]);
    }
}
