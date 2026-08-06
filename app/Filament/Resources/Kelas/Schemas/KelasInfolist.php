<?php

namespace App\Filament\Resources\Kelas\Schemas;

use App\Models\Kelas;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class KelasInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('nama_kelas')
                            ->label('')
                            ->size(TextSize::Large)
                            ->weight('bold'),

                        TextEntry::make('ringkasan_konteks')
                            ->label('')
                            ->state(fn(Kelas $record) => trim(
                                ($record->prodi?->nama_prodi ?? '-')
                                    . ' · Angkatan ' . $record->angkatan_id
                            ))
                            ->color('gray'),
                    ])
                    ->columnSpan(1),

                Section::make()
                    ->schema([
                        TextEntry::make('isi_kapasitas')
                            ->label('Jumlah Mahasiswa')
                            ->state(fn(Kelas $record) => "{$record->mahasiswa_kelas_aktif_count} / {$record->kapasitas} mahasiswa")
                            ->size(TextSize::Large)
                            ->weight('bold'),

                        TextEntry::make('status_kapasitas')
                            ->label('Status Kelas')
                            ->badge()
                            ->state(fn(Kelas $record) => self::hitungStatus($record))
                            ->color(fn(Kelas $record) => match (self::hitungStatus($record)) {
                                'Penuh' => 'danger',
                                'Hampir Penuh' => 'warning',
                                default => 'success',
                            }),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(2);
    }

    /**
     * Status dihitung dari rasio isi/kapasitas, bukan disimpan di DB,
     * supaya selalu akurat mengikuti data mahasiswa_kelas terkini.
     */
    protected static function hitungStatus(Kelas $record): string
    {
        $persen = $record->kapasitas > 0
            ? $record->mahasiswa_kelas_aktif_count / $record->kapasitas
            : 0;

        return match (true) {
            $persen >= 1 => 'Penuh',
            $persen >= 0.8 => 'Hampir Penuh',
            default => 'Tersedia',
        };
    }
}
