<?php

namespace App\Filament\Resources\KeuanganAdjustments\Schemas;

use App\Enums\Keuangan\StatusAdjustment;
use App\Filament\Resources\KeuanganAdjustments\KeuanganAdjustmentResource;
use App\Models\KeuanganAdjustment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KeuanganAdjustmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->schema([
                        TextEntry::make('nomor_adjustment')->label('Nomor ADJ')->weight('bold')->copyable(),
                        TextEntry::make('tagihan.kode_transaksi')->label('Tagihan Terkait')->copyable(),
                        TextEntry::make('tagihan.mahasiswa.person.nama_lengkap')->label('Nama Mahasiswa'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('jenis_adjustment')->badge(),
                        TextEntry::make('nominal')
                            ->formatStateUsing(fn($state) => 'Rp ' . number_format((float) $state, 2, ',', '.'))
                            ->color(fn($state) => (float) $state < 0 ? 'danger' : 'success'),
                        TextEntry::make('tindak_lanjut_kelebihan_bayar')->badge(),
                        TextEntry::make('keterangan')->columnSpanFull(),
                    ])->columns(3),

                Section::make('Jejak Audit (Maker-Checker)')
                    ->schema([
                        TextEntry::make('creator.name')->label('Dibuat Oleh'),
                        TextEntry::make('pengaju.name')->label('Diajukan Oleh'),
                        TextEntry::make('diajukan_at')->label('Tgl Diajukan')->dateTime('d M Y H:i:s'),
                        TextEntry::make('penyetuju.name')->label('Disetujui Oleh'),
                        TextEntry::make('disetujui_at')->label('Tgl Disetujui')->dateTime('d M Y H:i:s'),
                        TextEntry::make('diposting_at')->label('Tgl Diposting')->dateTime('d M Y H:i:s'),
                        TextEntry::make('catatan_approval')->label('Catatan Reviewer')->columnSpanFull(),
                    ])->columns(3),

                Section::make('Informasi Pembatalan')
                    ->schema([
                        TextEntry::make('pembatal.name')->label('Dibatalkan Oleh'),
                        TextEntry::make('dibatalkan_at')->label('Tgl Dibatalkan')->dateTime('d M Y H:i:s'),
                        TextEntry::make('alasan_pembatalan')->label('Alasan Batal'),
                        TextEntry::make('adjustmentPembalik.nomor_adjustment')
                            ->label('Nomor ADJ Pembalik')
                            ->color('info')
                            ->url(fn($record) => $record->adjustment_pembalik_id ? KeuanganAdjustmentResource::getUrl('view', ['record' => $record->adjustment_pembalik_id]) : null),
                    ])
                    ->columns(2)
                    ->visible(fn($record) => $record->status === StatusAdjustment::DIBATALKAN),
            ]);
    }
}
