<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikMode;
use App\Exceptions\PembimbingAkademikException;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\RefProdi;
use App\Models\RefTahunAkademik;
use App\Models\TrxDosen;
use App\Services\PembimbingAkademikService;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\HtmlString;

class PenugasanPembimbingPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'filament.clusters.pembimbing-akademik.pages.penugasan-pembimbing-page';
    protected static ?string $navigationLabel = 'Penugasan Pembimbing Akademik';
    protected static ?string $modelLabel = 'Penugasan Pembimbing Akademik';
    protected static ?string $clusterBreadcrumb = 'Penugasan Pembimbing Akademik';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Penugasan Pembimbing Akademik';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $cluster = PembimbingAkademikCluster::class;

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    /**
     * Menandai submit sedang berjalan, dipakai blade untuk disable tombol
     * & menampilkan spinner supaya user tidak klik dua kali (double submit).
     */
    public bool $isSubmitting = false;

    public function mount(): void
    {
        $this->form->fill([
            'jenis' => PembimbingAkademikJenis::DOSEN_WALI->value,
            'is_primary' => true,
            'tanggal_mulai' => now()->toDateString(),
            'semester_mulai_id' => RefTahunAkademik::query()->where('is_active', true)->value('id'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Jenis Pembimbing')
                        ->description('Tentukan jenis penugasan')
                        ->icon('heroicon-o-tag')
                        ->components([
                            Select::make('jenis')
                                ->label('Jenis Pembimbing')
                                ->options(PembimbingAkademikJenis::options())
                                ->native(false)
                                ->required()
                                ->live()
                                ->helperText('Pilih Dosen Wali bila ingin menugaskan wali kelas/mahasiswa. Jenis lain (skripsi, PKL, dll) selalu bersifat per-mahasiswa.'),

                            Select::make('prodi_id')
                                ->label('Program Studi')
                                ->options(fn() => RefProdi::query()->orderBy('nama_prodi')->pluck('nama_prodi', 'id'))
                                ->searchable()
                                ->live()
                                ->required(fn($get) => $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value)
                                ->visible(fn($get) => $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value)
                                ->helperText('Dipakai untuk membaca konfigurasi mode penugasan (per kelas / per mahasiswa) yang berlaku.'),

                            Select::make('angkatan_id')
                                ->label('Angkatan')
                                ->options(fn() => \App\Models\RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                                ->searchable()
                                ->live()
                                ->required(fn($get) => $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value)
                                ->visible(fn($get) => $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value),

                            Placeholder::make('info_konfigurasi')
                                ->label('')
                                ->visible(fn($get) => $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value && $get('prodi_id') && $get('angkatan_id'))
                                ->content(function ($get) {
                                    $konfigurasi = app(PembimbingAkademikService::class)
                                        ->konfigurasiAktif($get('prodi_id'), $get('angkatan_id'));

                                    if (! $konfigurasi) {
                                        return new HtmlString(
                                            '<div class="rounded-lg bg-danger-50 dark:bg-danger-500/10 p-3 text-sm text-danger-700 dark:text-danger-400">
                                                ⚠️ Belum ada konfigurasi mode yang <strong>aktif</strong> untuk kombinasi Program Studi &amp; Angkatan ini.
                                                Atur dulu di menu <strong>Konfigurasi Pembimbing</strong> sebelum melanjutkan.
                                            </div>'
                                        );
                                    }

                                    $mode = $konfigurasi->mode;

                                    return new HtmlString(
                                        '<div class="rounded-lg bg-success-50 dark:bg-success-500/10 p-3 text-sm text-success-700 dark:text-success-400">
                                            ✅ Mode aktif untuk kombinasi ini: <strong>' . e($mode->getLabel()) . '</strong>.
                                            Langkah berikutnya akan menyesuaikan otomatis.
                                        </div>'
                                    );
                                }),
                        ]),

                    Step::make('Target Penugasan')
                        ->description('Kelas atau mahasiswa')
                        ->icon('heroicon-o-user-group')
                        ->components([
                            Select::make('kelas_id')
                                ->label('Kelas')
                                ->searchable()
                                ->options(function ($get) {
                                    if (! $get('prodi_id') || ! $get('angkatan_id')) {
                                        return [];
                                    }

                                    return app(PembimbingAkademikService::class)
                                        ->kelasBelumPunyaWali((int) $get('prodi_id'), (int) $get('angkatan_id'));
                                })
                                ->helperText('Hanya menampilkan kelas yang belum memiliki Dosen Wali aktif.')
                                ->visible(fn($get) => $this->modeSaatIni($get) === PembimbingAkademikMode::PER_KELAS)
                                ->required(fn($get) => $this->modeSaatIni($get) === PembimbingAkademikMode::PER_KELAS),

                            Select::make('mahasiswa_id')
                                ->label('Mahasiswa')
                                ->searchable()
                                ->getSearchResultsUsing(function (string $search, $get) {
                                    return Mahasiswa::query()
                                        ->when(
                                            $get('jenis') === PembimbingAkademikJenis::DOSEN_WALI->value && $get('prodi_id') && $get('angkatan_id'),
                                            fn($q) => $q->where('prodi_id', $get('prodi_id'))->where('angkatan_id', $get('angkatan_id'))
                                        )
                                        ->where(fn($q) => $q
                                            ->where('nim', 'like', "%{$search}%")
                                            ->orWhereHas('person', fn($p) => $p->where('nama_lengkap', 'like', "%{$search}%")))
                                        ->limit(20)
                                        ->get()
                                        ->mapWithKeys(fn(Mahasiswa $m) => [$m->id => "{$m->nim} - {$m->person?->nama_lengkap}"]);
                                })
                                ->getOptionLabelUsing(fn($value) => optional(Mahasiswa::find($value))?->nim)
                                ->visible(fn($get) => $this->modeSaatIni($get) === PembimbingAkademikMode::PER_MAHASISWA)
                                ->required(fn($get) => $this->modeSaatIni($get) === PembimbingAkademikMode::PER_MAHASISWA),
                        ]),

                    Step::make('Detail & Konfirmasi')
                        ->description('Dosen, SK, dan ringkasan')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->components([
                            Select::make('dosen_id')
                                ->label('Dosen')
                                ->searchable()
                                ->getSearchResultsUsing(fn(string $search) => TrxDosen::query()
                                    ->where('nidn', 'like', "%{$search}%")
                                    ->orWhereHas('person', fn($q) => $q->where('nama_lengkap', 'like', "%{$search}%"))
                                    ->limit(20)
                                    ->get()
                                    ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap} ({$d->nidn})"]))
                                ->getOptionLabelUsing(fn($value) => optional(TrxDosen::find($value))?->nidn)
                                ->required(),
                            Toggle::make('is_primary')
                                ->label('Pembimbing Utama')
                                ->default(true),
                            Select::make('semester_mulai_id')
                                ->label('Semester Mulai')
                                ->searchable()
                                ->options(fn() => RefTahunAkademik::query()->orderByDesc('id')->pluck('nama_tahun', 'id'))
                                ->required(),
                            DatePicker::make('tanggal_mulai')
                                ->label('Tanggal Mulai')
                                ->default(now())
                                ->required(),
                            TextInput::make('nomor_sk')
                                ->label('Nomor SK')
                                ->maxLength(255),
                            DatePicker::make('tanggal_sk')
                                ->label('Tanggal SK'),
                            Textarea::make('keterangan')
                                ->label('Keterangan')
                                ->columnSpanFull()
                                ->rows(3),

                            Placeholder::make('ringkasan')
                                ->label('Ringkasan')
                                ->columnSpanFull()
                                ->content(function ($get) {
                                    $jenis = $get('jenis') ? PembimbingAkademikJenis::from($get('jenis'))->label() : '-';

                                    $target = $get('kelas_id')
                                        ? 'Kelas: ' . (Kelas::find($get('kelas_id'))?->nama_kelas ?? '-')
                                        : 'Mahasiswa: ' . (Mahasiswa::find($get('mahasiswa_id'))?->nim ?? '-');

                                    $dosen = $get('dosen_id') ? (TrxDosen::find($get('dosen_id'))?->person?->nama_lengkap ?? '-') : '-';

                                    return new HtmlString(
                                        '<div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 text-sm space-y-1">
                                            <div><strong>Jenis:</strong> ' . e($jenis) . '</div>
                                            <div><strong>Target:</strong> ' . e($target) . '</div>
                                            <div><strong>Dosen:</strong> ' . e($dosen) . '</div>
                                        </div>'
                                    );
                                }),
                        ]),
                ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /**
     * Mode efektif berdasarkan jenis + konfigurasi aktif prodi/angkatan.
     * Dipakai berulang kali di form() untuk show/hide & required field.
     */
    public function modeSaatIni($get): PembimbingAkademikMode
    {
        $jenisValue = $get('jenis');

        if (! $jenisValue) {
            return PembimbingAkademikMode::PER_MAHASISWA;
        }

        $jenis = PembimbingAkademikJenis::from($jenisValue);
        $konfigurasi = app(PembimbingAkademikService::class)->konfigurasiAktif($get('prodi_id'), $get('angkatan_id'));

        return app(PembimbingAkademikService::class)->modeUntuk($jenis, $konfigurasi);
    }

    public function submit(): void
    {
        $this->isSubmitting = true;

        try {
            $data = $this->form->getState();

            app(PembimbingAkademikService::class)->tugaskan($data);

            Notification::make()
                ->title('Pembimbing akademik berhasil ditugaskan')
                ->success()
                ->send();

            $this->form->fill([
                'jenis' => $data['jenis'],
                'prodi_id' => $data['prodi_id'] ?? null,
                'angkatan_id' => $data['angkatan_id'] ?? null,
                'is_primary' => true,
                'tanggal_mulai' => now()->toDateString(),
                'semester_mulai_id' => $data['semester_mulai_id'],
            ]);
        } catch (PembimbingAkademikException $e) {
            Notification::make()
                ->title('Tidak bisa menyimpan')
                ->body($e->getMessage())
                ->warning()
                ->send();
        } finally {
            $this->isSubmitting = false;
        }
    }
}
