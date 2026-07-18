<?php

declare(strict_types=1);

namespace App\Filament\Resources\Krs\Schemas;

use App\Models\JadwalKuliah;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Services\Akademik\KrsValidationService;
use Filament\Actions\Action as ActionsAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
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
                                    self::fetchMahasiswaData($set, $get);
                                }),

                            Select::make('tahun_akademik_id')
                                ->label('Tahun Akademik')
                                ->relationship('tahunAkademik', 'nama_tahun', fn($query) => $query->orderBy('tanggal_mulai', 'desc'))
                                ->required()
                                ->live()
                                ->afterStateUpdated(function(callable $set, callable $get) {
                                    self::fetchMahasiswaData($set, $get);
                                }),

                            Select::make('status_krs')
                                ->label('Status Awal')
                                ->options([
                                    'DRAFT' => 'Draft',
                                    'DIAJUKAN' => 'Diajukan',
                                    'DISETUJUI' => 'Langsung Disetujui (Bypass)',
                                ])
                                ->default('DISETUJUI')
                                ->required(),

                            // State tersembunyi untuk menyimpan metadata
                            Hidden::make('is_eligible')->default(true),
                            Hidden::make('validation_msg')->default(null),
                            Hidden::make('active_kelas_id'),
                            Hidden::make('mode_krs')->default('PAKET'),
                            Hidden::make('prodi_id'),

                            // Alert Peringatan UI
                            Placeholder::make('peringatan_kontinuitas')
                                ->label('Status Validasi Akademik')
                                ->visible(fn (callable $get) => $get('is_eligible') === false)
                                ->content(fn (callable $get) => new HtmlString(
                                    '<div class="p-4 mb-4 text-sm text-danger-800 rounded-lg bg-danger-50" role="alert">' .
                                    '<span class="font-bold">Perhatian:</span> ' . $get('validation_msg') .
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
                        ->description('Pilih Kelas Utama & Mengulang')
                        ->schema([
                            // 1. KELAS PAKET / UTAMA
                            Section::make('Mata Kuliah Paket (Kelas Utama)')
                                ->description('Mata kuliah yang ditawarkan untuk kelas mahasiswa saat ini.')
                                ->schema([
                                    CheckboxList::make('jadwal_kuliah_ids')
                                        ->label('')
                                        ->options(function (callable $get) {
                                            $taId = $get('tahun_akademik_id');
                                            $kelasId = $get('active_kelas_id');

                                            if (!$taId || !$kelasId) return [];

                                            return JadwalKuliah::with(['mataKuliah', 'kelas'])
                                                ->where('tahun_akademik_id', $taId)
                                                ->where('kelas_id', $kelasId)
                                                ->get()
                                                ->mapWithKeys(fn($jadwal) => [
                                                    $jadwal->id => "{$jadwal->mataKuliah->kode_mk} - {$jadwal->mataKuliah->nama_mk} ({$jadwal->mataKuliah->sks_default} SKS) | Kuota: {$jadwal->isi_kelas}/{$jadwal->kuota_kelas}"
                                                ]);
                                        })
                                        ->default(function (callable $get) {
                                            if ($get('mode_krs') !== 'PAKET') return [];
                                            
                                            return JadwalKuliah::where('tahun_akademik_id', $get('tahun_akademik_id'))
                                                ->where('kelas_id', $get('active_kelas_id'))
                                                ->pluck('id')
                                                ->toArray();
                                        })
                                        ->disabled(fn(callable $get) => $get('mode_krs') === 'PAKET')
                                        ->dehydrated(true)
                                        ->helperText(fn(callable $get) => $get('mode_krs') === 'PAKET' 
                                            ? 'Mode PAKET: Kelas otomatis terpilih sesuai kurikulum mahasiswa.' 
                                            : null)
                                        ->columns(1)
                                        ->searchable(),
                                ]),

                            // 2. KELAS MENGULANG / LINTAS KELAS
                            Section::make('Mata Kuliah Mengulang / Lintas Kelas (Opsional)')
                                ->description('Pilih kelas dari angkatan/prodi lain untuk mengulang.')
                                ->schema([
                                    CheckboxList::make('jadwal_mengulang_ids')
                                        ->label('')
                                        ->options(function (callable $get) {
                                            $taId = $get('tahun_akademik_id');
                                            $kelasId = $get('active_kelas_id');
                                            $prodiId = $get('prodi_id');

                                            if (!$taId || !$prodiId) return [];

                                            return JadwalKuliah::with(['mataKuliah', 'kelas'])
                                                ->where('tahun_akademik_id', $taId)
                                                ->whereHas('kelas', fn($q) => $q->where('prodi_id', $prodiId))
                                                ->where('kelas_id', '!=', $kelasId)
                                                ->get()
                                                ->mapWithKeys(fn($jadwal) => [
                                                    $jadwal->id => "{$jadwal->mataKuliah->kode_mk} - {$jadwal->mataKuliah->nama_mk} ({$jadwal->mataKuliah->sks_default} SKS) | Kelas: {$jadwal->kelas->nama_kelas} | Kuota: {$jadwal->isi_kelas}/{$jadwal->kuota_kelas}"
                                                ]);
                                        })
                                        ->columns(1)
                                        ->searchable(),
                                ])->collapsed(),
                        ]),
                ])->columnSpanFull()
            ]);
    }

    private static function fetchMahasiswaData(callable $set, callable $get): void
    {
        // Reset checkbox
        $set('jadwal_kuliah_ids', []);
        $set('jadwal_mengulang_ids', []);
        
        $mahasiswaId = $get('mahasiswa_id');
        $taId = $get('tahun_akademik_id');

        if ($mahasiswaId) {
            $mahasiswa = Mahasiswa::with('kurikulum')->find($mahasiswaId);
            
            // Set metadata dasar mahasiswa
            $set('prodi_id', $mahasiswa->prodi_id ?? null);
            $set('mode_krs', $mahasiswa->kurikulum->mode_krs ?? 'PAKET');
            
            // Cari kelas aktif mahasiswa
            $activeKelasId = DB::table('mahasiswa_kelas')
                ->where('mahasiswa_id', $mahasiswaId)
                ->whereNull('tanggal_keluar')
                ->value('kelas_id');
                
            $set('active_kelas_id', $activeKelasId);

            // Validasi kelayakan jika TA juga sudah dipilih
            if ($taId) {
                $ta = RefTahunAkademik::find($taId);
                if ($ta) {
                    $service = app(KrsValidationService::class);
                    // Kita hanya set false jika admin perlu tahu ada tunggakan/status tidak aktif.
                    // Namun di admin, biasanya admin bisa mem-bypass ini.
                    $result = $service->checkStatusMahasiswa($mahasiswa, $ta);
                    
                    if (!$result->passed) {
                        $set('is_eligible', false);
                        $set('validation_msg', $result->message . ' (Admin dapat mengabaikan peringatan ini).');
                    } else {
                        // Jika lolos checkStatus, opsional bisa cek keuangan juga disini
                        $set('is_eligible', true);
                        $set('validation_msg', null);
                    }
                }
            }
        }
    }
}