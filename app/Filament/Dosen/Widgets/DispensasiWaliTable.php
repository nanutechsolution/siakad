<?php

declare(strict_types=1);

namespace App\Filament\Dosen\Widgets;

use App\Models\DispensasiAkademik;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class DispensasiWaliTable extends BaseWidget
{
    protected static ?string $heading = 'Dispensasi KRS Mahasiswa Perwalian';
    
    // Widget tabel ini hanya muncul jika ada data, agar dashboard tidak penuh
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $dosenId = Auth::user()?->person?->trxDosen?->id;

        return $table
            ->query(
                DispensasiAkademik::query()
                    ->where('jenis', 'KRS')
                    // Hanya memfilter mahasiswa yang memiliki kelas dengan Dosen Wali ini
                    ->whereHas('mahasiswa.kelas.kelasDosenWalis', function (Builder $q) use ($dosenId) {
                        $q->where('dosen_id', $dosenId)->where('is_primary', true);
                    })
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mahasiswa.person.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alasan')
                    ->label('Alasan Dispensasi')
                    ->limit(50),
                Tables\Columns\TextColumn::make('berlaku_sampai')
                    ->label('Batas Waktu')
                    ->date('d M Y')
                    ->color(fn ($record) => $record->berlaku_sampai < now() ? 'danger' : 'gray'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'DRAFT',
                        'success' => 'AKTIF',
                        'danger' => ['EXPIRED', 'DIBATALKAN'],
                    ]),
            ]);
    }
}