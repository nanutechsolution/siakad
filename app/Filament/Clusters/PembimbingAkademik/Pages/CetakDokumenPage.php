<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Domain\Authorization\Services\FormResolver;
use App\Enums\PembimbingAkademikStatus;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\PembimbingAkademik;
use App\Models\RefAngkatan;
use App\Models\TrxDosen;
use App\Services\PembimbingAkademikPdfService;
use App\Support\Utf8;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

class CetakDokumenPage extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use HasPageShield;

    protected string $view = 'filament.clusters.pembimbing-akademik.pages.cetak-dokumen-page';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-printer';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationLabel = 'Cetak Dokumen';
    protected static ?string $title = 'Cetak Dokumen Pembimbing Akademik';
    protected static ?string $cluster = PembimbingAkademikCluster::class;

    protected function dosenLabel(TrxDosen $dosen): string
    {
        $nama = Utf8::clean($dosen->person?->nama_dengan_gelar ?? $dosen->person?->nama_lengkap);
        $identifier = $dosen->nidn
            ? 'NIDN ' . $dosen->nidn
            : ($dosen->nuptk ? 'NUPTK ' . $dosen->nuptk : 'ID belum diisi');

        return "{$nama} — {$identifier}";
    }

    protected function dosenSearchField(string $name = 'dosen_id', string $label = 'Dosen'): Select
    {
        return Select::make($name)
            ->label($label)
            ->searchable()
            ->getSearchResultsUsing(function (string $search): array {
                $term = trim($search);
                $accessible = app(FormResolver::class)->accessibleProdiIds(auth()->user());

                return TrxDosen::query()
                    ->with(['person.gelars', 'prodi'])
                    ->where('is_active', true)
                    ->whereIn('prodi_id', $accessible)
                    ->where(function (Builder $query) use ($term): void {
                        $query
                            ->where('nidn', 'like', "%{$term}%")
                            ->orWhere('nuptk', 'like', "%{$term}%")
                            ->orWhereHas('person', fn(Builder $person) => $person
                                ->where('nama_lengkap', 'like', "%{$term}%"));
                    })
                    ->limit(20)
                    ->get()
                    ->mapWithKeys(fn(TrxDosen $dosen) => [$dosen->id => $this->dosenLabel($dosen)])
                    ->all();
            })
            ->getOptionLabelUsing(function ($value): ?string {
                $dosen = TrxDosen::with(['person.gelars', 'prodi'])->find($value);

                return $dosen ? $this->dosenLabel($dosen) : null;
            })
            ->helperText('Cari berdasarkan nama, NIDN, atau NUPTK. Data dosen aktif sesuai kewenangan Anda.')
            ->required();
    }

    protected function penugasanSearchField(): Select
    {
        return Select::make('pembimbing_akademik_id')
            ->label('Penugasan Aktif')
            ->searchable()
            ->getSearchResultsUsing(function (string $search): array {
                $term = trim($search);
                $accessible = app(FormResolver::class)->accessibleProdiIds(auth()->user());

                return PembimbingAkademik::query()
                    ->with(['mahasiswa.person', 'mahasiswa.prodi', 'kelas.prodi', 'dosen.person.gelars'])
                    ->where('status', PembimbingAkademikStatus::AKTIF)
                    ->where(function (Builder $query) use ($term, $accessible): void {
                        $query
                            ->whereHas('mahasiswa', fn(Builder $q) => $q
                                ->whereIn('prodi_id', $accessible)
                                ->where(function (Builder $m) use ($term): void {
                                    $m->where('nim', 'like', "%{$term}%")
                                        ->orWhereHas('person', fn(Builder $p) => $p
                                            ->where('nama_lengkap', 'like', "%{$term}%"));
                                }))
                            ->orWhereHas('kelas', fn(Builder $q) => $q
                                ->whereIn('prodi_id', $accessible)
                                ->where('nama_kelas', 'like', "%{$term}%"));
                    })
                    ->limit(20)
                    ->get()
                    ->mapWithKeys(fn(PembimbingAkademik $assignment) => [
                        $assignment->id => $this->penugasanLabel($assignment),
                    ])
                    ->all();
            })
            ->getOptionLabelUsing(function ($value): ?string {
                $assignment = PembimbingAkademik::with([
                    'mahasiswa.person',
                    'mahasiswa.prodi',
                    'kelas.prodi',
                    'dosen.person.gelars',
                ])->find($value);

                return $assignment ? $this->penugasanLabel($assignment) : null;
            })
            ->helperText('Cari dengan NIM, nama mahasiswa, atau nama kelas.')
            ->required();
    }

    protected function penugasanLabel(PembimbingAkademik $assignment): string
    {
        $target = $assignment->mahasiswa
            ? $assignment->mahasiswa->nim . ' — ' . Utf8::clean($assignment->mahasiswa->person?->nama_lengkap)
            : 'Kelas ' . Utf8::clean($assignment->kelas?->nama_kelas ?? '-');

        $dosen = $assignment->dosen ? $this->dosenLabel($assignment->dosen) : 'Dosen belum tersedia';

        return "{$target} · {$dosen}";
    }

    public function skIndividuAction(): Action
    {
        return Action::make('skIndividu')
            ->label('Cetak SK Individu')
            ->icon('heroicon-o-document-text')
            ->color('primary')
            ->modalHeading('Cetak SK Penugasan Individu')
            ->modalDescription('Pilih satu penugasan Dosen Wali aktif. SK resmi akan memakai penomoran, QR, dan penandatangan yang terkonfigurasi.')
            ->modalSubmitActionLabel('Cetak SK')
            ->schema([$this->penugasanSearchField()])
            ->action(function (array $data) {
                try {
                    $assignment = PembimbingAkademik::with(['mahasiswa.prodi', 'kelas.prodi'])
                        ->whereKey($data['pembimbing_akademik_id'])
                        ->where('status', PembimbingAkademikStatus::AKTIF)
                        ->firstOrFail();

                    $this->assertAssignmentAccessible($assignment);

                    // Response WAJIB di-return; kalau dibuang Livewire tidak
                    // mengirim file sama sekali dan pengguna tidak melihat apa-apa.
                    return app(PembimbingAkademikPdfService::class)->downloadSkPenugasan($assignment);
                } catch (\Throwable $e) {
                    $this->sendErrorNotification('SK Individu', $e->getMessage());

                    return null;
                }
            });
    }

    public function skMassalDosenAction(): Action
    {
        return Action::make('skMassalDosen')
            ->label('Cetak SK Massal')
            ->icon('heroicon-o-document-duplicate')
            ->color('primary')
            ->modalHeading('Cetak SK Massal per Dosen')
            ->modalDescription('Satu PDF berisi seluruh penugasan aktif dosen yang dipilih.')
            ->modalSubmitActionLabel('Cetak SK Massal')
            ->schema([$this->dosenSearchField('dosen_id', 'Dosen')])
            ->action(function (array $data) {
                try {
                    $dosen = TrxDosen::query()->whereKey($data['dosen_id'])->where('is_active', true)->firstOrFail();
                    $this->assertDosenAccessible($dosen);

                    return app(PembimbingAkademikPdfService::class)->downloadSkMassalDosen($dosen->id);
                } catch (\Throwable $e) {
                    $this->sendErrorNotification('SK Massal', $e->getMessage());

                    return null;
                }
            });
    }

    public function daftarPembimbingAction(): Action
    {
        return Action::make('daftarPembimbing')
            ->label('Cetak Rekap Pembimbing')
            ->icon('heroicon-o-clipboard-document-list')
            ->color('gray')
            ->modalHeading('Cetak Rekap Pembimbing Aktif')
            ->modalDescription('Filter opsional. Jika kosong, rekap mencakup seluruh Prodi yang boleh Anda akses.')
            ->modalSubmitActionLabel('Cetak Rekap')
            ->schema([
                Select::make('prodi_id')
                    ->label('Program Studi')
                    ->options(fn() => app(FormResolver::class)->prodiOptions(auth()->user()))
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('angkatan_id')
                    ->label('Angkatan')
                    ->options(fn() => RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                    ->searchable()
                    ->nullable(),
            ])
            ->action(function (array $data) {
                try {
                    if (filled($data['prodi_id'] ?? null)) {
                        $allowed = app(FormResolver::class)->accessibleProdiIds(auth()->user());
                        abort_unless(in_array((int) $data['prodi_id'], $allowed, true), 403);
                    }

                    return app(PembimbingAkademikPdfService::class)->downloadDaftarPembimbing($data);
                } catch (\Throwable $e) {
                    $this->sendErrorNotification('Rekap Pembimbing', $e->getMessage());

                    return null;
                }
            });
    }

    public function bimbinganDosenAction(): Action
    {
        return Action::make('bimbinganDosen')
            ->label('Cetak Daftar Bimbingan')
            ->icon('heroicon-o-identification')
            ->color('gray')
            ->modalHeading('Cetak Daftar Bimbingan Dosen')
            ->modalDescription('Daftar mahasiswa/kelas yang dibimbing, cocok sebagai lampiran kinerja atau BKD.')
            ->modalSubmitActionLabel('Cetak Daftar')
            ->schema([$this->dosenSearchField()])
            ->action(function (array $data) {
                try {
                    $dosen = TrxDosen::query()->whereKey($data['dosen_id'])->firstOrFail();
                    $this->assertDosenAccessible($dosen);

                    return app(PembimbingAkademikPdfService::class)->downloadDaftarBimbinganDosen($dosen->id);
                } catch (\Throwable $e) {
                    $this->sendErrorNotification('Daftar Bimbingan', $e->getMessage());

                    return null;
                }
            });
    }

    public function laporanMonitoringAction(): Action
    {
        return Action::make('laporanMonitoring')
            ->label('Cetak Laporan Monitoring')
            ->icon('heroicon-o-document-chart-bar')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Cetak Laporan Monitoring')
            ->modalDescription('Berisi statistik pembimbing akademik dan daftar mahasiswa yang belum memiliki Dosen Wali.')
            ->modalSubmitActionLabel('Cetak Laporan')
            ->action(function () {
                try {
                    return app(PembimbingAkademikPdfService::class)->downloadLaporanMonitoring();
                } catch (\Throwable $e) {
                    $this->sendErrorNotification('Laporan Monitoring', $e->getMessage());

                    return null;
                }
            });
    }

    protected function assertDosenAccessible(TrxDosen $dosen): void
    {
        $allowed = app(FormResolver::class)->accessibleProdiIds(auth()->user());

        if (! in_array((int) $dosen->prodi_id, $allowed, true)) {
            throw new RuntimeException('Dosen tersebut berada di luar Program Studi yang dapat Anda akses.');
        }
    }

    protected function assertAssignmentAccessible(PembimbingAkademik $assignment): void
    {
        $prodiId = $assignment->mahasiswa?->prodi_id ?? $assignment->kelas?->prodi_id;
        $allowed = app(FormResolver::class)->accessibleProdiIds(auth()->user());

        if (! $prodiId || ! in_array((int) $prodiId, $allowed, true)) {
            throw new RuntimeException('Penugasan tersebut berada di luar Program Studi yang dapat Anda akses.');
        }
    }

    protected function sendErrorNotification(string $document, string $message): void
    {
        Notification::make()
            ->title("Gagal mencetak {$document}")
            ->body($message)
            ->danger()
            ->duration(8000)
            ->send();
    }
}
