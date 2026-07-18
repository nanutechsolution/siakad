<?php

namespace App\Filament\Resources\Mahasiswas\Tables;

use App\Domain\Authorization\Services\FormResolver;
use App\Models\Kelas;
use App\Models\KeuanganKomponenBiaya;
use App\Models\RefTahunAkademik;
use App\Models\TagihanMahasiswa;
use App\Models\TagihanMahasiswaDetail;
use App\Services\ManajemenKelasService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MahasiswasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['person', 'prodi', 'angkatan', 'program']))
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('person.nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('program.nama_program')
                    ->label('Kelas')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('angkatan_id')
                    ->label('Angkatan')
                    ->relationship('angkatan', 'id_tahun')
                    ->searchable(),
                SelectFilter::make('program_id')
                    ->label('Program Kelas')
                    ->relationship('program', 'nama_program'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('terbitkanTagihanSPP')
                        ->label('Terbitkan Tagihan')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn($record) => Str::startsWith($record->nim, 'PMB'))
                        ->schema([
                            Select::make('tahun_akademik_id')
                                ->label('Tahun Akademik')
                                ->options(RefTahunAkademik::where('is_active', 1)->pluck('nama_tahun', 'id'))
                                ->required()
                                ->searchable(),
                            Select::make('komponen_biaya_id')
                                ->label('Komponen Biaya (SPP/Pangkal)')
                                ->options(KeuanganKomponenBiaya::where('is_active', 1)->pluck('nama_komponen', 'id'))
                                ->required()
                                ->searchable(),
                            TextInput::make('nominal_dasar')
                                ->label('Nominal Tagihan (Rp)')
                                ->numeric()
                                ->prefix('Rp')
                                ->required()
                                ->minValue(1000)
                                ->default(5000000), // Angka default, admin BAUK bisa ubah
                        ])
                        ->action(function (array $data, $record) {
                            try {
                                DB::beginTransaction();

                                // 1. Ambil nama komponen untuk snapshot
                                $komponen = KeuanganKomponenBiaya::findOrFail($data['komponen_biaya_id']);

                                // 2. Generate Invoice Unik (INV-TahunBulanTanggal-Random4Huruf)
                                $kodeTransaksi = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4));

                                // 3. Buat Data Induk Tagihan
                                $tagihan = TagihanMahasiswa::create([
                                    'mahasiswa_id'      => $record->id,
                                    'tahun_akademik_id' => $data['tahun_akademik_id'],
                                    'kode_transaksi'    => $kodeTransaksi,
                                    'deskripsi'         => 'Tagihan Pembayaran Awal Camaba',
                                    'total_tagihan'     => $data['nominal_dasar'],
                                    'status_bayar'      => 'BELUM',
                                    'created_by'        => auth()->id(),
                                    'tenggat_waktu'     => now()->addDays(14), // Batas waktu bayar 14 hari
                                ]);

                                // 4. Buat Rincian Tagihan
                                TagihanMahasiswaDetail::create([
                                    'tagihan_id'             => $tagihan->id,
                                    'komponen_biaya_id'      => $komponen->id,
                                    'nama_komponen_snapshot' => $komponen->nama_komponen,
                                    'nominal_dasar'          => $data['nominal_dasar'],
                                    'nominal_diskon'         => 0,
                                    'nominal_terbayar'       => 0,
                                ]);

                                DB::commit();

                                Notification::make()
                                    ->title('Berhasil!')
                                    ->body("Tagihan {$kodeTransaksi} berhasil diterbitkan untuk Camaba ini.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                DB::rollBack();

                                Notification::make()
                                    ->title('Gagal Menerbitkan Tagihan')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('pindah_kelas')
                        ->label('Pindah Kelas (Auto-Exit)')
                        ->icon('heroicon-o-arrow-path-rounded-square')
                        ->form([
                            Select::make('kelas_tujuan_id')
                                ->label('Pilih Kelas Tujuan')
                                ->options(Kelas::query()->pluck('nama_kelas', 'id'))
                                ->required(),
                            DatePicker::make('tanggal_pindah')
                                ->default(now())
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $service = app(ManajemenKelasService::class);

                            foreach ($records as $record) {
                                $service->pindahKelas(
                                    $record->id,
                                    $data['kelas_tujuan_id'],
                                    $data['tanggal_pindah']
                                );
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Berhasil Dipindahkan')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
