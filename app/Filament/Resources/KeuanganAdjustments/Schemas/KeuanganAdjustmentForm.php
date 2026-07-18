<?php

namespace App\Filament\Resources\KeuanganAdjustments\Schemas;

use App\Enums\Keuangan\JenisAdjustment;
use App\Enums\Keuangan\StatusAdjustment;
use App\Enums\Keuangan\TindakLanjutKelebihanBayar;
use App\Models\TagihanMahasiswa;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KeuanganAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tagihan')
                    ->schema([
                        Select::make('tagihan_id')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                return TagihanMahasiswa::query()
                                    ->with('mahasiswa.person')
                                    ->where(function ($q) use ($search) {
                                        $q->where('kode_transaksi', 'like', "%{$search}%")
                                            ->orWhereHas('mahasiswa.person', function ($q) use ($search) {
                                                $q->where('nama_lengkap', 'like', "%{$search}%");
                                            });
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($tagihan) => [
                                        $tagihan->id => "{$tagihan->kode_transaksi} - {$tagihan->mahasiswa?->person?->nama_lengkap}"
                                    ]);
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $tagihan = TagihanMahasiswa::with('mahasiswa.person')->find($value);

                                return $tagihan
                                    ? "{$tagihan->kode_transaksi} - {$tagihan->mahasiswa?->person?->nama_lengkap}"
                                    : null;
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disabled(fn(?Model $record): bool => $record !== null && $record->status !== StatusAdjustment::DRAFT),

                        Grid::make(3)->schema([
                            TextEntry::make('info_total_tagihan')
                                ->label('Total Tagihan Saat Ini')
                                ->state(function (Get $get) {
                                    $tagihan = TagihanMahasiswa::find($get('tagihan_id'));
                                    return $tagihan ? 'Rp ' . number_format((float) $tagihan->total_tagihan, 2, ',', '.') : '-';
                                }),
                            TextEntry::make('info_total_bayar')
                                ->label('Total Telah Dibayar')
                                ->state(function (Get $get) {
                                    $tagihan = TagihanMahasiswa::find($get('tagihan_id'));
                                    return $tagihan ? 'Rp ' . number_format((float) $tagihan->total_bayar, 2, ',', '.') : '-';
                                }),
                            TextEntry::make('info_status_bayar')
                                ->label('Status Pembayaran')
                                ->state(function (Get $get) {
                                    $tagihan = TagihanMahasiswa::find($get('tagihan_id'));
                                    return $tagihan ? $tagihan->status_bayar : '-';
                                }),
                        ]),
                    ]),

                Section::make('Detail Penyesuaian')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('nomor_adjustment')
                            ->label('Nomor Adjustment')
                            ->default('Auto-generated')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('jenis_adjustment')
                            ->label('Jenis Penyesuaian')
                            ->options(JenisAdjustment::class)
                            ->required()
                            ->disabled(fn(?Model $record): bool => $record !== null && $record->status !== StatusAdjustment::DRAFT),

                        TextInput::make('nominal')
                            ->label('Nominal Penyesuaian (Rp)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->helperText('Gunakan tanda minus (-) untuk mengurangi tagihan (misal: -500000). Nilai positif untuk menambah tagihan.')
                            ->disabled(fn(?Model $record): bool => $record !== null && $record->status !== StatusAdjustment::DRAFT)
                            ->rules([
                                fn(Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                                    $tagihan = TagihanMahasiswa::find($get('tagihan_id'));
                                    if (! $tagihan) return;

                                    $newTotal = (float) $tagihan->total_tagihan + (float) $value;
                                    if ($newTotal < 0) {
                                        $fail("Total tagihan akhir tidak boleh negatif. Maksimal pengurangan adalah Rp " . number_format((float) $tagihan->total_tagihan, 0, ',', '.'));
                                    }
                                },
                            ]),

                        Select::make('tindak_lanjut_kelebihan_bayar')
                            ->label('Tindak Lanjut Kelebihan Bayar')
                            ->options(TindakLanjutKelebihanBayar::class)
                            ->visible(function (Get $get) {
                                $tagihan = TagihanMahasiswa::find($get('tagihan_id'));
                                $nominal = (float) $get('nominal');
                                if (! $tagihan || $nominal === 0.0) return false;

                                $newTotal = (float) $tagihan->total_tagihan + $nominal;
                                return (float) $tagihan->total_bayar > $newTotal;
                            })
                            ->required(function (Get $get) {
                                $tagihan = TagihanMahasiswa::find($get('tagihan_id'));
                                $nominal = (float) $get('nominal');
                                if (! $tagihan) return false;
                                return (float) $tagihan->total_bayar > ((float) $tagihan->total_tagihan + $nominal);
                            })
                            ->disabled(fn(?Model $record): bool => $record !== null && $record->status !== StatusAdjustment::DRAFT)
                            ->helperText('Peringatan: Penyesuaian ini menyebabkan tagihan baru lebih kecil dari uang yang sudah dibayarkan mahasiswa. Silakan pilih cara penyelesaiannya.'),

                        Textarea::make('keterangan')
                            ->label('Keterangan / Alasan')
                            ->required()
                            ->minLength(10)
                            ->disabled(fn(?Model $record): bool => $record !== null && $record->status !== StatusAdjustment::DRAFT),
                    ]),
            ]);
    }
}
