<?php

namespace App\Filament\Dosen\Resources\MahasiswaBimbingans\Tables;

use App\Models\Krs;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MahasiswaBimbingansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nim')->label('NIM')->searchable(),
                TextColumn::make('person.nama_lengkap')->label('Nama')->searchable(),
                TextColumn::make('prodi.nama_prodi')->label('Prodi'),
                TextColumn::make('status_krs_terakhir')
                    ->label('Status KRS Aktif')
                    ->badge()
                    ->state(fn($record) => Krs::where('mahasiswa_id', $record->id)
                        ->where('tahun_akademik_id', \App\Models\RefTahunAkademik::where('is_active', 1)->first()?->id)
                        ->value('status_krs') ?? 'BELUM AJUAN')
                    ->color(fn($state) => match ($state) {
                        'DISETUJUI' => 'success',
                        'DIAJUKAN' => 'warning',
                        default => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('status_krs')
                    ->label('Status Pengajuan KRS')
                    ->options([
                        'DIAJUKAN' => 'Menunggu Persetujuan',
                        'DISETUJUI' => 'Sudah Disetujui',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) return $query;

                        return $query->whereHas('krs', function ($q) use ($data) {
                            $q->where('tahun_akademik_id', \App\Models\RefTahunAkademik::where('is_active', 1)->value('id'))
                                ->where('status_krs', $data['value']);
                        });
                    }),

                // 2. Filter Angkatan
                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->relationship('angkatan', 'id_tahun') // Pastikan relasi 'angkatan' ada di Model Mahasiswa
                    ->searchable()
                    ->preload(),

                // 3. Filter Program Studi (Jika dosen mengampu mahasiswa lintas prodi)
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama_prodi')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('setujui_krs')
                    ->label('Review & Setujui')
                    ->icon('heroicon-o-document-magnifying-glass')
                    ->color('success')
                    // Menggunakan modalContent untuk menampilkan isi KRS sebelum setuju
                    ->modalContent(function ($record) {
                        $activeTa = \App\Models\RefTahunAkademik::where('is_active', 1)->first();
                        $krs = Krs::where('mahasiswa_id', $record->id)
                            ->where('tahun_akademik_id', $activeTa->id)
                            ->first();

                        if (!$krs) return 'KRS tidak ditemukan.';

                        // Ambil detail MK
                        $details = \App\Models\KrsDetail::where('krs_id', $krs->id)->get();

                        return view('filament.dosen.components.review-krs-modal', [
                            'details' => $details,
                            'krs' => $krs
                        ]);
                    })
                    ->modalSubmitActionLabel('Setujui KRS Ini')
                    ->requiresConfirmation()
                    ->modalHeading('Review KRS Mahasiswa')
                    ->visible(fn($record) => Krs::where('mahasiswa_id', $record->id)
                        ->where('tahun_akademik_id', \App\Models\RefTahunAkademik::where('is_active', 1)->first()?->id)
                        ->where('status_krs', 'DIAJUKAN')
                        ->exists())
                    ->action(function ($record) {
                        // Logika update status tetap sama...
                        $activeTa = \App\Models\RefTahunAkademik::where('is_active', 1)->first();
                        $krs = Krs::where('mahasiswa_id', $record->id)->where('tahun_akademik_id', $activeTa->id)->first();

                        if ($krs) {
                            $krs->update(['status_krs' => 'DISETUJUI', 'disetujui_oleh' => Auth::user()->person_id, 'disetujui_pada' => now()]);
                            // Log insert...
                            Notification::make()->success()->title('KRS Disetujui')->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
