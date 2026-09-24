<?php

namespace App\Filament\Resources\Mahasiswas\Pages\Concerns;

use App\Domain\Authorization\Services\FormResolver;
use App\Exceptions\ManajemenKelasException;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Services\Akademik\NimService;
use App\Services\Kelas\ManajemenKelasService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

trait HasMahasiswaMutasiProdiAction
{
    protected function makeMutasiProdiAction(): Action
    {
        return Action::make('mutasiProdi')
            ->label('Mutasi Prodi')
            ->icon('heroicon-o-arrow-path-rounded-square')
            ->color('warning')
            ->authorize('update')
            ->slideOver()
            ->modalHeading('Mutasi Program Studi')
            ->modalDescription('Mutasi akan membuat NIM baru sesuai format Prodi tujuan dan memindahkan mahasiswa ke kelas tujuan.')
            ->schema([
                Select::make('prodi_id')
                    ->label('Program Studi Tujuan')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (callable $set): void {
                        $set('kurikulum_id', null);
                        $set('kelas_id', null);
                    }),

                Select::make('kurikulum_id')
                    ->label('Kurikulum Tujuan')
                    ->relationship(
                        name: 'kurikulum',
                        titleAttribute: 'nama_kurikulum',
                        modifyQueryUsing: fn(Builder $query, Get $get) => $query
                            ->when(
                                filled($get('prodi_id')),
                                fn(Builder $query) => $query->where('prodi_id', $get('prodi_id')),
                            ),
                    )
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->nullable()
                    ->disabled(fn(Get $get): bool => blank($get('prodi_id'))),

                Select::make('kelas_id')
                    ->label('Kelas Tujuan')
                    ->options(function (Get $get, Mahasiswa $record): array {
                        $prodiId = $get('prodi_id');

                        if (blank($prodiId)) {
                            return [];
                        }

                        $accessibleIds = app(FormResolver::class)->accessibleProdiIds(auth()->user());

                        return Kelas::query()
                            ->with('prodi')
                            ->whereIn('prodi_id', $accessibleIds)
                            ->where('prodi_id', (int) $prodiId)
                            ->where('angkatan_id', $record->angkatan_id)
                            ->orderBy('nama_kelas')
                            ->get()
                            ->mapWithKeys(function (Kelas $kelas): array {
                                $service = app(ManajemenKelasService::class);
                                $jumlah = $service->jumlahAnggotaAktif($kelas->id);
                                $kapasitas = $kelas->kapasitas === null ? '' : "/{$kelas->kapasitas}";

                                return [
                                    $kelas->id => sprintf(
                                        '%s — %s — Angkatan %s (%d%s)',
                                        $kelas->prodi?->kode_prodi_internal ?? '-',
                                        $kelas->nama_kelas,
                                        $kelas->angkatan_id,
                                        $jumlah,
                                        $kapasitas,
                                    ),
                                ];
                            })
                            ->all();
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled(fn(Get $get): bool => blank($get('prodi_id'))),

                TextEntry::make('nim_preview')
                    ->label('NIM Baru (otomatis)')
                    ->helperText('NIM baru akan menggantikan NIM lama saat mutasi disimpan.')
                    ->getStateUsing(function (Get $get, Mahasiswa $record): string {
                        $prodiId = $get('prodi_id');

                        if (blank($prodiId)) {
                            return 'Pilih Prodi tujuan untuk melihat preview NIM.';
                        }

                        $prodi = RefProdi::find($prodiId);
                        $nim = app(NimService::class)->preview($record, $prodi);

                        return $nim ?? 'NIM belum dapat dibuat.';
                    })
                    ->color('warning'),

                DatePicker::make('tanggal_mutasi')
                    ->label('Tanggal Mutasi')
                    ->default(now())
                    ->native(false)
                    ->required(),
            ])
            ->action(function (array $data, Mahasiswa $record): void {
                $user = auth()->user();
                $resolver = app(FormResolver::class);
                $prodiId = (int) ($data['prodi_id'] ?? 0);
                $kelasId = (int) ($data['kelas_id'] ?? 0);

                if (! $user || ! $resolver->isProdiAccessible($user, $prodiId)) {
                    throw ValidationException::withMessages([
                        'prodi_id' => 'Program Studi tujuan tidak dapat diakses.',
                    ]);
                }

                $kelas = Kelas::query()
                    ->whereKey($kelasId)
                    ->whereIn('prodi_id', $resolver->accessibleProdiIds($user))
                    ->where('prodi_id', $prodiId)
                    ->where('angkatan_id', $record->angkatan_id)
                    ->first();

                if (! $kelas) {
                    throw ValidationException::withMessages([
                        'kelas_id' => 'Kelas tujuan tidak valid untuk Prodi dan angkatan mahasiswa.',
                    ]);
                }

                try {
                    app(ManajemenKelasService::class)->mutasiProdi(
                        $record,
                        $prodiId,
                        $kelasId,
                        filled($data['kurikulum_id'] ?? null) ? (int) $data['kurikulum_id'] : null,
                        $data['tanggal_mutasi'],
                    );

                    Notification::make()
                        ->title('Mutasi Prodi berhasil')
                        ->body("NIM baru mahasiswa: {$record->fresh()->nim}")
                        ->success()
                        ->send();
                } catch (ManajemenKelasException $exception) {
                    Notification::make()
                        ->title('Mutasi Prodi gagal')
                        ->body($exception->getMessage())
                        ->warning()
                        ->send();
                }
            });
    }
}
