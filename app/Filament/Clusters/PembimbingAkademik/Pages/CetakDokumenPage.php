<?php

namespace App\Filament\Clusters\PembimbingAkademik\Pages;

use App\Enums\PembimbingAkademikStatus;
use App\Filament\Clusters\PembimbingAkademik\PembimbingAkademikCluster;
use App\Models\PembimbingAkademik;
use App\Models\RefAngkatan;
use App\Models\RefProdi;
use App\Models\TrxDosen;
use App\Services\PembimbingAkademikPdfService;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

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
    protected function dosenSearchField(string $name = 'dosen_id', string $label = 'Dosen'): Select
    {
        return Select::make($name)
            ->label($label)
            ->searchable()
            ->getSearchResultsUsing(fn(string $search) => TrxDosen::query()
                ->where('nidn', 'like', "%{$search}%")
                ->orWhereHas('person', fn($p) => $p->where('nama_lengkap', 'like', "%{$search}%"))
                ->limit(20)
                ->get()
                ->mapWithKeys(fn(TrxDosen $d) => [$d->id => "{$d->person?->nama_lengkap} ({$d->nidn})"]))
            ->getOptionLabelUsing(fn($value) => optional(TrxDosen::find($value))?->nidn)
            ->required();
    }

    public function skIndividuAction(): Action
    {
        return Action::make('skIndividu')
            ->label('Cetak SK')
            ->icon('heroicon-o-document-text')
            ->schema([
                Select::make('pembimbing_akademik_id')
                    ->label('Pilih Penugasan Aktif')
                    ->searchable()
                    ->getSearchResultsUsing(fn(string $search) => PembimbingAkademik::query()
                        ->where('status', PembimbingAkademikStatus::AKTIF)
                        ->where(fn($q) => $q
                            ->whereHas('mahasiswa', fn($m) => $m->where('nim', 'like', "%{$search}%"))
                            ->orWhereHas('kelas', fn($k) => $k->where('nama_kelas', 'like', "%{$search}%"))
                            ->orWhereHas('dosen.person', fn($p) => $p->where('nama_lengkap', 'like', "%{$search}%")))
                        ->limit(20)
                        ->get()
                        ->mapWithKeys(fn(PembimbingAkademik $r) => [
                            $r->id => ($r->mahasiswa ? $r->mahasiswa->nim : $r->kelas?->nama_kelas) . ' — ' . $r->dosen?->person?->nama_lengkap,
                        ]))
                    ->getOptionLabelUsing(function ($value) {
                        $r = PembimbingAkademik::find($value);

                        if (! $r) {
                            return null;
                        }

                        return ($r->mahasiswa ? $r->mahasiswa->nim : $r->kelas?->nama_kelas) . ' — ' . $r->dosen?->person?->nama_lengkap;
                    })
                    ->required(),
            ])
            ->action(fn(array $data) => app(PembimbingAkademikPdfService::class)
                ->downloadSkPenugasan(PembimbingAkademik::findOrFail($data['pembimbing_akademik_id'])));
    }

    public function skMassalDosenAction(): Action
    {
        return Action::make('skMassalDosen')
            ->label('Cetak SK Massal')
            ->icon('heroicon-o-document-duplicate')
            ->form([
                $this->dosenSearchField('dosen_id', 'Dosen')
                    ->helperText('Satu file PDF berisi seluruh penugasan aktif dosen ini.'),
            ])
            ->action(fn(array $data) => app(PembimbingAkademikPdfService::class)
                ->downloadSkMassalDosen($data['dosen_id']));
    }

    public function daftarPembimbingAction(): Action
    {
        return Action::make('daftarPembimbing')
            ->label('Cetak Daftar')
            ->icon('heroicon-o-clipboard-document-list')
            ->form([
                Select::make('prodi_id')
                    ->label('Program Studi (opsional)')
                    ->options(fn() => RefProdi::query()->orderBy('nama_prodi')->pluck('nama_prodi', 'id'))
                    ->searchable(),
                Select::make('angkatan_id')
                    ->label('Angkatan (opsional)')
                    ->options(fn() => RefAngkatan::query()->orderByDesc('id_tahun')->pluck('id_tahun', 'id_tahun'))
                    ->searchable(),
            ])
            ->action(fn(array $data) => app(PembimbingAkademikPdfService::class)->downloadDaftarPembimbing($data));
    }

    public function bimbinganDosenAction(): Action
    {
        return Action::make('bimbinganDosen')
            ->label('Cetak Daftar Bimbingan')
            ->icon('heroicon-o-identification')
            ->form([
                $this->dosenSearchField('dosen_id', 'Dosen')
                    ->helperText('Daftar seluruh mahasiswa/kelas yang dibimbing dosen ini — cocok untuk lampiran laporan kinerja.'),
            ])
            ->action(fn(array $data) => app(PembimbingAkademikPdfService::class)
                ->downloadDaftarBimbinganDosen($data['dosen_id']));
    }

    public function laporanMonitoringAction(): Action
    {
        return Action::make('laporanMonitoring')
            ->label('Cetak Laporan')
            ->icon('heroicon-o-document-chart-bar')
            ->color('gray')
            ->requiresConfirmation()
            ->modalDescription('Laporan berisi ringkasan statistik + daftar lengkap mahasiswa yang belum punya Dosen Wali saat ini.')
            ->action(fn() => app(PembimbingAkademikPdfService::class)->downloadLaporanMonitoring());
    }
}
