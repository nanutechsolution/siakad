<?php

namespace App\Filament\Dosen\Resources\MahasiswaBimbingans\Tables;

use App\Enums\KrsStatusEnum;
use App\Filament\Support\HasKrsReviewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MahasiswaBimbingansTable
{
    use HasKrsReviewAction;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('person.nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable(),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Prodi'),

                TextColumn::make('krs.status_krs')
                    ->label('Status KRS Aktif')
                    ->badge()
                    ->state(fn(Model $record) => self::krsOf($record)?->status_krs?->value ?? 'BELUM AJUAN')
                    ->color(fn(string $state) => KrsStatusEnum::tryFrom($state)?->getColor() ?? 'gray'),

                TextColumn::make('status_risiko')
                    ->label('Risiko Akademik')
                    ->badge()
                    ->state(fn(Model $record) => $record->statusRisiko)
                    ->color(fn($state) => $state?->getColor() ?? 'gray')
                    ->icon(fn($state) => $state?->getIcon()),

                TextColumn::make('tunggakan')
                    ->label('Tunggakan')
                    ->state(fn(Model $record) => $record->totalTunggakan())
                    ->money('IDR')
                    ->color(fn(Model $record) => $record->totalTunggakan() > 0 ? 'danger' : 'success')
                    ->weight(fn(Model $record) => $record->totalTunggakan() > 0 ? 'bold' : 'normal'),
            ])
            ->filters([
                SelectFilter::make('status_krs')
                    ->label('Status Pengajuan KRS')
                    ->options([
                        KrsStatusEnum::DIAJUKAN->value => 'Menunggu Persetujuan',
                        KrsStatusEnum::DISETUJUI->value => 'Sudah Disetujui',
                        KrsStatusEnum::DITOLAK->value => 'Ditolak',
                        KrsStatusEnum::DIBATALKAN->value => 'Dibatalkan',
                    ])
                    // Default dikosongkan supaya mahasiswa dengan status apa pun
                    // tetap tampil saat halaman dibuka.
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('krs', function ($q) use ($data) {
                            $q->where('status_krs', $data['value']);
                        });
                    }),
                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->relationship('angkatan', 'id_tahun')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                // Review slide-over (Setujui/Tolak/Lihat Detail) dibagikan
                // dengan panel Admin lewat HasKrsReviewAction.
                static::makeKrsReviewAction(),
            ]);
    }
}
