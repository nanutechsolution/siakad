<?php

namespace App\Filament\Resources\MahasiswaBeasiswas\Tables;

use App\Models\RefTahunAkademik;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MahasiswaBeasiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mahasiswa.person.nama_lengkap')
                    ->label('Nama Mahasiswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('beasiswa.nama_beasiswa')
                    ->label('Program Beasiswa')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('tahunAkademikMulai.nama_tahun')
                    ->label('Mulai')
                    ->sortable(),

                TextColumn::make('tahunAkademikAkhir.nama_tahun')
                    ->label('Selesai')
                    ->placeholder('Tanpa Batas')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                SelectFilter::make('beasiswa_id')
                    ->label('Filter Beasiswa')
                    ->relationship('beasiswa', 'nama_beasiswa')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('prodi')
                    ->label('Filter Prodi')
                    ->relationship('mahasiswa.prodi', 'nama_prodi') // Asumsi ada relasi prodi di model Mahasiswa
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('hentikan')
                        ->label('Hentikan')
                        ->icon('heroicon-o-stop-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Hentikan Pemberian Beasiswa')
                        ->modalDescription('Tindakan ini akan menonaktifkan beasiswa dan menetapkan tahun akademik saat ini sebagai batas akhirnya. Tindakan ini aman untuk rekam jejak finansial.')
                        ->visible(fn(Model $record): bool => $record->is_active && auth()->user()->can('HentikanBeasiswa'))
                        ->action(function (Model $record) {
                            $tahunAktif = RefTahunAkademik::where('is_active', true)->first();

                            $record->update([
                                'is_active' => false,
                                'tahun_akademik_akhir_id' => $tahunAktif?->id ?? $record->tahun_akademik_akhir_id,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Beasiswa Dihentikan')
                                ->body('Status beasiswa telah dinonaktifkan secara historis.')
                                ->send();
                        }),

                    // Delete Guard: Mencegah penghapusan jika ada indikasi telah masuk tagihan
                    DeleteAction::make()
                        ->before(function (Action $action, Model $record) {
                            // Secara konseptual, jika beasiswa dibuat di masa lalu (sebelum hari ini) 
                            // dan tagihan mahasiswa telah di-generate, kita blokir hard delete-nya.
                            // Idealnya Anda mengecek ke tabel `tagihan_mahasiswas_details` di sini.
                            if ($record->created_at && $record->created_at->diffInDays(now()) > 1) {
                                Notification::make()
                                    ->danger()
                                    ->title('Penghapusan Ditolak')
                                    ->body('Data beasiswa ini kemungkinan sudah terikat dengan riwayat tagihan. Harap gunakan fitur "Hentikan Beasiswa" alih-alih menghapusnya.')
                                    ->persistent()
                                    ->send();

                                $action->halt();
                            }
                        }),
                ])

            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
