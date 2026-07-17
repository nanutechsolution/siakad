<?php

namespace App\Filament\Mahasiswa\Resources\TagihanNonRegulers\Schemas;

use App\Models\TagihanNonReguler;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TagihanNonRegulerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    ViewEntry::make('summary')
                        ->hiddenLabel()
                        ->view(
                            'filament.mahasiswa.tagihan-non-reguler.summary'
                        ),
                ]),

            Section::make('Rincian Komponen Biaya')
                ->schema([
                    ViewEntry::make('details')
                        ->hiddenLabel()
                        ->view(
                            'filament.mahasiswa.tagihan-non-reguler.details'
                        ),
                ]),


            Section::make('Riwayat Pembayaran')
                ->schema([
                    ViewEntry::make('payments')
                        ->hiddenLabel()
                        ->view(
                            'filament.mahasiswa.tagihan-non-reguler.payments'
                        ),
                ]),

            Section::make('Konfirmasi Pembayaran')
                ->schema([
                    ViewEntry::make('upload')
                        ->hiddenLabel()
                        ->view(
                            'filament.mahasiswa.tagihan-non-reguler.upload'
                        ),
                ])
                ->visible(
                    fn($record) =>
                    $record->status_bayar !== 'LUNAS'
                ),

        ]);
    }
}
