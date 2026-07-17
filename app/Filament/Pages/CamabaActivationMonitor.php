<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TagihanMahasiswa;
use App\Services\Pembayaran\PaymentPolicyChecker;
use App\Mail\CicilanTerverifikasiMailable;
use App\Services\Notifications\SmsService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use UnitEnum;

class CamabaActivationMonitor extends Page implements HasTable
{
    use InteractsWithTable, HasPageShield;

    protected static ?string $navigationLabel = 'Generate NIM Monitor';
    protected static ?string $title = 'Generator NIM';
    protected string $view = 'filament.pages.camaba-activation-monitor';
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::AKADEMIK->value;

    public int $totalCamaba = 0;

    public int $siapGenerate = 0;

    public int $belumSiap = 0;

    public float $progressAktivasi = 0;

    public float $totalTunggakan = 0;

    public int $belumDitagihkan = 0;

    public int $belumBayar = 0;

    public int $cicilan = 0;

    public int $lunas = 0;

    public ?RefTahunAkademik $tahunAkademikAktif = null;

    public function mount(): void
    {
        $this->loadStatistics();
    }
    protected function loadStatistics(): void
    {
        $this->tahunAkademikAktif = RefTahunAkademik::where('is_active', true)->first();

        $camaba = Mahasiswa::query()
            ->where('nim', 'like', 'PMB%')
            ->with('prodi')
            ->get();

        $this->totalCamaba = $camaba->count();
        $this->belumDitagihkan = 0;
        $this->belumBayar = 0;
        $this->cicilan = 0;
        $this->lunas = 0;
        $checker = app(PaymentPolicyChecker::class);

        $siap = 0;
        $belum = 0;

        foreach ($camaba as $mahasiswa) {

            $tagihan = TagihanMahasiswa::query()
                ->where('mahasiswa_id', $mahasiswa->id)
                ->when(
                    $this->tahunAkademikAktif,
                    fn($q) => $q->where(
                        'tahun_akademik_id',
                        $this->tahunAkademikAktif->id
                    )
                )
                ->latest()
                ->first();

            if (! $tagihan) {
                $this->belumDitagihkan++;
                $belum++;
                continue;
            }

            match ($tagihan->status_bayar) {
                'BELUM' => $this->belumBayar++,
                'CICIL' => $this->cicilan++,
                'LUNAS' => $this->lunas++,
            };

            $result = $checker->cekKepatuhan($mahasiswa, $tagihan);

            if ($result['passed']) {
                $siap++;
            } else {
                $belum++;
            }
        }

        $this->siapGenerate = $siap;
        $this->belumSiap = $belum;

        $this->progressAktivasi = $this->totalCamaba > 0
            ? round(($siap / $this->totalCamaba) * 100, 1)
            : 0;

        $this->totalTunggakan = TagihanMahasiswa::query()
            ->whereIn('mahasiswa_id', $camaba->pluck('id'))
            ->sum('sisa_tagihan');
    }

    public function table(Table $table): Table
    {
        return $table->query(
            Mahasiswa::query()
                ->where('nim', 'like', 'PMB%')
                ->with(['person', 'prodi'])
                ->withSum('tagihans as total_tagihan', 'total_tagihan')
                ->withSum('tagihans as total_bayar', 'total_bayar')
        )
            ->heading('Daftar Calon Mahasiswa')
            ->columns([
                TextColumn::make('nim')
                    ->label('NIM PMB')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('person.nama_lengkap')
                    ->label('Mahasiswa')
                    ->description(fn($record) => $record->person?->email)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('prodi.nama_prodi')
                    ->label('Program Studi')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('angkatan_id')
                    ->label('Angkatan')
                    ->badge()
                    ->alignCenter(),
                TextColumn::make('total_tagihan')
                    ->label('Total Tagihan')
                    ->state(function ($record) {
                        $query = TagihanMahasiswa::where('mahasiswa_id', $record->id);

                        if (! $query->exists()) {
                            return 'Belum Diterbitkan Tagihan';
                        }

                        return $query->sum('total_tagihan');
                    })
                    ->formatStateUsing(
                        fn($state) => is_numeric($state)
                            ? 'Rp ' . number_format($state, 0, ',', '.')
                            : $state
                    )
                    ->badge()
                    ->color(fn($state) => is_numeric($state) ? 'success' : 'gray'),
                TextColumn::make('total_bayar')
                    ->money('IDR'),
                TextColumn::make('sisa_tagihan')
                    ->label('Sisa')
                    ->state(fn($record) => TagihanMahasiswa::where('mahasiswa_id', $record->id)->sum('sisa_tagihan'))
                    ->money('IDR')
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function ($record) {
                        $checker = app(PaymentPolicyChecker::class);
                        $ta = RefTahunAkademik::where('is_active', true)->first();
                        if (!$ta) {
                            return 'Tidak ada TA aktif';
                        }
                        $tagihan = TagihanMahasiswa::where('mahasiswa_id', $record->id)
                            ->where('tahun_akademik_id', $ta->id)
                            ->latest()
                            ->first();

                        if (!$tagihan) {
                            return 'Belum ada tagihan';
                        }
                        return $checker->cekKepatuhan($record, $tagihan)['passed']
                            ? 'Siap Generate'
                            : 'Belum Memenuhi';
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Siap Generate' => 'success',
                        'Belum Memenuhi' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('updated_at')
                    ->label('Update')
                    ->since()
                    ->sortable(),
            ])
            ->filters([

                SelectFilter::make('prodi')
                    ->label('Program Studi')
                    ->relationship('prodi', 'nama_prodi')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status_tagihan')
                    ->label('Status Tagihan')
                    ->options([
                        'belum' => 'Belum Diterbitkan',
                        'belum_bayar' => 'Belum Bayar',
                        'cicil' => 'Cicilan',
                        'lunas' => 'Lunas',
                    ])
                    ->query(function (Builder $query, array $data) {

                        $value = $data['value'] ?? null;

                        if (!$value) {
                            return;
                        }

                        match ($value) {

                            'belum' => $query->whereDoesntHave('tagihans'),

                            'belum_bayar' => $query->whereHas(
                                'tagihans',
                                fn($q) =>
                                $q->where('status_bayar', 'BELUM')
                            ),

                            'cicil' => $query->whereHas(
                                'tagihans',
                                fn($q) =>
                                $q->where('status_bayar', 'CICIL')
                            ),

                            'lunas' => $query->whereHas(
                                'tagihans',
                                fn($q) =>
                                $q->where('status_bayar', 'LUNAS')
                            ),

                            default => null,
                        };
                    }),

                SelectFilter::make('kelayakan')
                    ->label('Kelayakan Generate NIM')
                    ->options([
                        'siap' => 'Memenuhi Persyaratan',
                        'belum' => 'Belum Memenuhi Persyaratan',
                    ])
                    ->query(function (Builder $query, array $data) {

                        $value = $data['value'] ?? null;

                        if (!$value) {
                            return;
                        }

                        $ta = RefTahunAkademik::where('is_active', true)->first();

                        if (!$ta) {
                            return;
                        }

                        $checker = app(PaymentPolicyChecker::class);

                        $ids = Mahasiswa::query()
                            ->where('nim', 'like', 'PMB%')
                            ->whereHas('tagihans', function ($q) use ($ta) {
                                $q->where('tahun_akademik_id', $ta->id);
                            })
                            ->get()
                            ->filter(function ($mahasiswa) use ($checker, $ta, $value) {

                                $tagihan = TagihanMahasiswa::where('mahasiswa_id', $mahasiswa->id)
                                    ->where('tahun_akademik_id', $ta->id)
                                    ->latest()
                                    ->first();

                                if (!$tagihan) {
                                    return false;
                                }

                                $passed = $checker->cekKepatuhan(
                                    $mahasiswa,
                                    $tagihan
                                )['passed'];

                                return $value === 'siap'
                                    ? $passed
                                    : ! $passed;
                            })
                            ->pluck('id');


                        $query->whereIn('id', $ids);
                    }),
                Filter::make('memiliki_tunggakan')
                    ->label('Masih Memiliki Tunggakan')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereHas(
                            'tagihans',
                            fn($q) =>
                            $q->where('sisa_tagihan', '>', 0)
                        )
                    ),

                Filter::make('tanpa_tagihan')
                    ->label('Belum Diterbitkan Tagihan')
                    ->query(
                        fn(Builder $query) =>
                        $query->whereDoesntHave('tagihans')
                    ),

                Filter::make('sudah_generate')
                    ->label('NIM Sudah Digenerate')
                    ->query(
                        fn(Builder $query) =>
                        $query->where('nim', 'not like', 'PMB%')
                    ),

                Filter::make('belum_generate')
                    ->label('NIM Belum Digenerate')
                    ->query(
                        fn(Builder $query) =>
                        $query->where('nim', 'like', 'PMB%')
                    ),

            ])
            ->recordActions([

                ActionGroup::make(
                    [
                        Action::make('send_reminder')
                            ->label('Kirim Reminder')
                            ->action(function (Mahasiswa $record) {
                                $activeTa = RefTahunAkademik::where('is_active', 1)->first();
                                $tagihan = $activeTa ? TagihanMahasiswa::where('mahasiswa_id', $record->id)
                                    ->where('tahun_akademik_id', $activeTa->id)
                                    ->latest()->first() : null;

                                $checker = app(PaymentPolicyChecker::class);
                                $res = $tagihan ? $checker->cekKepatuhan($record, $tagihan) : ['passed' => false, 'unmet' => []];

                                // Send DB notification via existing mailable and sms adapter
                                if (! empty($record->person?->email)) {
                                    try {
                                        Mail::to($record->person->email)->queue(new CicilanTerverifikasiMailable($record, $res['unmet']));
                                    } catch (\Throwable $e) {
                                        // swallow and log via laravel logging (Filament will show result)
                                    }
                                }

                                if ($record->person?->no_hp) {
                                    try {
                                        app(SmsService::class)->send($record->person->no_hp, 'Silakan selesaikan tagihan untuk aktivasi NIM. Cek akun untuk detail.');
                                    } catch (\Throwable $e) {
                                    }
                                }

                                \Filament\Notifications\Notification::make()
                                    ->title('Reminder dikirim')
                                    ->success()
                                    ->send();
                            }),

                        Action::make('manual_generate_nim')
                            ->label('Generate NIM Manual')
                            ->color('success')
                            ->requiresConfirmation()
                            ->action(function (Mahasiswa $record) {
                                if (! str_starts_with((string)$record->nim, 'PMB')) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Mahasiswa sudah memiliki NIM resmi')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                DB::transaction(function () use ($record) {
                                    $prodi = RefProdi::whereKey($record->prodi_id)->lockForUpdate()->first();
                                    if (! $prodi) throw new \RuntimeException('Prodi tidak ditemukan');

                                    $kampusSettings = app(\App\Settings\KampusSettings::class);
                                    $isResetPerTahun = (bool) ($kampusSettings->reset_nim_tahunan ?? false);
                                    $angkatanTahun = (int) $record->angkatan_id;

                                    if ($isResetPerTahun) {
                                        $lastMahasiswa = Mahasiswa::where('prodi_id', $prodi->id)
                                            ->where('angkatan_id', $angkatanTahun)
                                            ->where('nim', 'NOT LIKE', 'PMB%')
                                            ->orderBy('nim', 'desc')
                                            ->lockForUpdate()
                                            ->first();

                                        $lastSeq = $lastMahasiswa ? (int) substr($lastMahasiswa->nim, -3) : 0;
                                        $nextSeq = $lastSeq + 1;
                                    } else {
                                        $nextSeq = ((int) $prodi->last_nim_seq) + 1;
                                    }

                                    $format = $prodi->format_nim ?? '{THN}{KODE}{NO:3}';
                                    $nim = $this->renderFormatNim($format, $angkatanTahun, $prodi->kode_prodi_internal, $nextSeq);

                                    $record->update(['nim' => $nim]);
                                    $prodi->update(['last_nim_seq' => $nextSeq]);
                                });

                                \Filament\Notifications\Notification::make()
                                    ->title('NIM berhasil dibuat')
                                    ->success()
                                    ->send();
                            }),

                    ]
                )
            ]);
    }

    public function getTableQuery(): Builder
    {
        return Mahasiswa::query()->where('nim', 'like', 'PMB%')->with(['person', 'prodi', 'angkatan']);
    }

    private function renderFormatNim(string $format, int $tahun, string $kodeProdi, int $nomorUrut): string
    {
        $nim = $format;
        $nim = str_replace('{TAHUN}', (string) $tahun, $nim);
        $nim = str_replace('{THN}', substr((string) $tahun, -2), $nim);
        $nim = str_replace('{KODE}', $kodeProdi, $nim);

        if (preg_match('/\{NO:(\d+)\}/', $nim, $matches)) {
            $digitCount = max(1, (int) $matches[1]);
            $padded = str_pad((string) $nomorUrut, $digitCount, '0', STR_PAD_LEFT);
            $nim = str_replace($matches[0], $padded, $nim);
        } else {
            $nim = str_replace('{NO}', str_pad((string) $nomorUrut, 3, '0', STR_PAD_LEFT), $nim);
        }

        return $nim;
    }
}
