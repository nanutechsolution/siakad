<?php

namespace App\Filament\Resources\DosenKetersediaans\Tables;

use App\Models\DosenKetersediaan;
use App\Models\TrxDosen;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DosenKetersediaansTable
{
    /**
     * Peta warna badge per hari — sekadar bantuan visual, tidak
     * mempengaruhi data yang dikirim ke scheduler.
     */
    private const WARNA_HARI = [
        'Senin' => 'info',
        'Selasa' => 'success',
        'Rabu' => 'warning',
        'Kamis' => 'primary',
        'Jumat' => 'danger',
        'Sabtu' => 'gray',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dosen.person.nama_dengan_gelar')
                    ->label('Dosen')
                    ->searchable(
                        query: fn(Builder $query, string $search): Builder => $query->whereHas(
                            'dosen.person',
                            fn(Builder $query) => $query->where('nama_lengkap', 'like', "%{$search}%"),
                        ),
                    )
                    ->sortable(false)
                    ->description(fn(DosenKetersediaan $record) => $record->dosen?->nidn ? "NIDN: {$record->dosen->nidn}" : null),

                TextColumn::make('hari')
                    ->label('Hari')
                    ->badge()
                    ->color(fn(string $state): string => self::WARNA_HARI[$state] ?? 'gray')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        $urutan = implode(',', array_map(fn($hari) => "'{$hari}'", DosenKetersediaan::HARI_URUTAN));

                        return $query->orderByRaw("FIELD(hari, {$urutan}) {$direction}");
                    }),

                TextColumn::make('jam_mulai')
                    ->label('Jam Mulai')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('jam_selesai')
                    ->label('Jam Selesai')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('durasi')
                    ->label('Durasi')
                    ->state(function (DosenKetersediaan $record): string {
                        $mulai = \Carbon\Carbon::parse($record->jam_mulai);
                        $selesai = \Carbon\Carbon::parse($record->jam_selesai);
                        $menit = $mulai->diffInMinutes($selesai);

                        $jam = intdiv($menit, 60);
                        $sisaMenit = $menit % 60;

                        return $sisaMenit > 0 ? "{$jam}j {$sisaMenit}m" : "{$jam} jam";
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(function (Builder $query): Builder {
                $urutan = implode(',', array_map(fn($hari) => "'{$hari}'", DosenKetersediaan::HARI_URUTAN));

                return $query->orderByRaw("FIELD(hari, {$urutan})")->orderBy('jam_mulai');
            })
            ->filters([
                SelectFilter::make('dosen_id')
                    ->label('Dosen')
                    ->searchable()
                    ->options(fn(): array => TrxDosen::query()
                        ->with('person.gelars')
                        ->get()
                        ->mapWithKeys(fn(TrxDosen $dosen) => [
                            $dosen->getKey() => $dosen->person?->nama_dengan_gelar ?? $dosen->getKey(),
                        ])
                        ->all()),

                SelectFilter::make('hari')
                    ->label('Hari')
                    ->options(array_combine(DosenKetersediaan::HARI_URUTAN, DosenKetersediaan::HARI_URUTAN)),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Ketersediaan Dosen')
                    ->modalDescription('Yakin ingin menghapus data ketersediaan ini? Tindakan ini akan mempengaruhi hasil generate jadwal berikutnya.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada data ketersediaan dosen')
            ->emptyStateDescription('Tambahkan jadwal ketersediaan agar dosen dapat ditempatkan oleh generator jadwal.')
            ->emptyStateIcon(Heroicon::OutlinedClock);
    }
}
