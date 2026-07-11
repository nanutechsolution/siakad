<?php

declare(strict_types=1);

namespace App\Filament\Resources\Krs\Schemas;

use App\Models\JadwalKuliah;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Services\Akademik\KrsValidationService;
use Filament\Actions\Action as ActionsAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class KrsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Identitas')
                        ->description('Pilih Mahasiswa & Periode')
                        ->schema([
                            Select::make('mahasiswa_id')
                                ->label('Mahasiswa')
                                ->options(
                                    Mahasiswa::with('person')
                                        ->limit(100)
                                        ->get()
                                        ->mapWithKeys(fn($m) => [$m->id => $m->nim . ' - ' . ($m->person->nama_lengkap ?? 'Unknown')])
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function(callable $set, callable $get) {
                                    self::validateKontinuitas($set, $get);
                                }),

                            Select::make('tahun_akademik_id')
                                ->label('Tahun Akademik')
                                ->relationship('tahunAkademik', 'nama_tahun', fn($query) => $query->orderBy('tanggal_mulai', 'desc'))
                                ->required()
                                ->live()
                                ->afterStateUpdated(function(callable $set, callable $get) {
                                    self::validateKontinuitas($set, $get);
                                }),

                            Select::make('status_krs')
                                ->label('Status Awal')
                                ->options([
                                    'DRAFT' => 'Draft',
                                    'DISETUJUI' => 'Langsung Disetujui (Bypass)',
                                ])
                                ->default('DRAFT')
                                ->required(),

                            // State tersembunyi untuk menyimpan hasil validasi service
                            Hidden::make('is_eligible')->default(true),
                            Hidden::make('validation_msg')->default(null),

                            // Alert Peringatan UI
                            Placeholder::make('peringatan_kontinuitas')
                                ->label('Status Validasi Akademik')
                                ->visible(fn (callable $get) => $get('is_eligible') === false)
                                ->content(fn (callable $get) => new HtmlString(
                                    '<div class="p-4 mb-4 text-sm text-danger-800 rounded-lg bg-danger-50 dark:bg-gray-800 dark:text-danger-400" role="alert">' .
                                    '<span class="font-bold">Terblokir:</span> ' . $get('validation_msg') .
                                    '</div>'
                                )),

                            // Tombol Shortcut ke pembuatan Dispensasi
                            Actions::make([
                                ActionsAction::make('buat_dispensasi')
                                    ->label('Buat Dispensasi Akademik (KRS)')
                                    ->icon('heroicon-o-document-plus')
                                    ->color('warning')
                                    ->url(fn (callable $get) => '/admin/dispensasi-akademiks/create?mahasiswa_id=' . $get('mahasiswa_id'))
                                    ->openUrlInNewTab(),
                            ])->visible(fn (callable $get) => $get('is_eligible') === false),
                        ]),

                    Wizard\Step::make('Mata Kuliah')
                        ->description('Pilih Kelas yang Lolos Prasyarat')
                        ->schema([
                            CheckboxList::make('jadwal_kuliah_ids')
                                ->label('Jadwal Kuliah Tersedia')
                                ->options(function (callable $get) {
                                    $mahasiswaId = $get('mahasiswa_id');
                                    $taId = $get('tahun_akademik_id');

                                    if (!$mahasiswaId || !$taId || $get('is_eligible') === false) {
                                        return [];
                                    }

                                    $mahasiswa = Mahasiswa::find($mahasiswaId);
                                    if (!$mahasiswa) return [];

                                    $jadwals = JadwalKuliah::with(['mataKuliah', 'kelas'])
                                        ->where('tahun_akademik_id', $taId)
                                        ->get();

                                    $service = app(KrsValidationService::class);
                                    $eligibleOptions = [];

                                    foreach ($jadwals as $jadwal) {
                                        $checkPrasyarat = $service->checkPrasyarat($mahasiswa, [$jadwal->id]);

                                        if ($checkPrasyarat->passed) {
                                            $label = "{$jadwal->mataKuliah->kode_mk} - {$jadwal->mataKuliah->nama_mk} ({$jadwal->mataKuliah->sks_default} SKS) | Kelas: {$jadwal->kelas->nama_kelas} | Kuota: {$jadwal->isi_kelas}/{$jadwal->kuota_kelas}";
                                            $eligibleOptions[$jadwal->id] = $label;
                                        }
                                    }

                                    return $eligibleOptions;
                                })
                                ->columns(1)
                                ->bulkToggleable()
                                ->searchable(),
                        ]),
                ])->columnSpanFull()
            ]);
    }

    private static function validateKontinuitas(callable $set, callable $get): void
    {
        // Reset checkbox jadwal jika identitas berubah
        $set('jadwal_kuliah_ids', []);
        
        $mahasiswaId = $get('mahasiswa_id');
        $taId = $get('tahun_akademik_id');

        if ($mahasiswaId && $taId) {
            $mahasiswa = Mahasiswa::find($mahasiswaId);
            $ta = RefTahunAkademik::find($taId);

            if ($mahasiswa && $ta) {
                $service = app(KrsValidationService::class);
                $result = $service->checkStatusMahasiswa($mahasiswa, $ta);

                $set('is_eligible', $result->passed);
                $set('validation_msg', $result->message);
                return;
            }
        }

        $set('is_eligible', true);
        $set('validation_msg', null);
    }
}