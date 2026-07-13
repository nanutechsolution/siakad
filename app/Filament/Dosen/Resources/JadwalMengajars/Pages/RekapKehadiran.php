<?php

namespace App\Filament\Dosen\Resources\JadwalMengajars\Pages;

use App\Enums\StatusKehadiranEnum;
use App\Filament\Dosen\Resources\JadwalMengajars\JadwalMengajarResource;
use App\Models\KrsDetail;
use App\Models\PerkuliahanAbsensi;
use App\Models\PerkuliahanSesi;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RekapKehadiran extends Page
{
    use InteractsWithRecord;

    protected static string $resource = JadwalMengajarResource::class;

    protected string $view = 'filament.dosen.resources.jadwal-mengajars.pages.rekap-kehadiran';
    public Collection $sesiList;
    public Collection $mahasiswaList;
    public array $matrix = [];

    public int $ambangBatasPersen = 75;
    public float $rataRataKehadiran = 0;
    public int $mahasiswaBerisiko = 0;


    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetakPdf')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->action('cetakPdf'),
        ];
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->loadMissing(['mataKuliah', 'kelas', 'tahunAkademik']);

        $this->sesiList = PerkuliahanSesi::query()
            ->where('jadwal_kuliah_id', $this->record->id)
            ->where('status_sesi', 'selesai')
            ->orderBy('pertemuan_ke')
            ->get(['id', 'pertemuan_ke', 'waktu_mulai_rencana']);

        $this->mahasiswaList = KrsDetail::query()
            ->where('jadwal_kuliah_id', $this->record->id)
            ->with(['krs.mahasiswa.person'])
            ->get()
            ->sortBy(fn($kd) => $kd->krs->mahasiswa->person->nama_lengkap ?? '');

        $absensis = PerkuliahanAbsensi::query()
            ->whereIn('perkuliahan_sesi_id', $this->sesiList->pluck('id'))
            ->whereIn('krs_detail_id', $this->mahasiswaList->pluck('id'))
            ->get()
            ->groupBy('krs_detail_id');
        $this->rataRataKehadiran = $this->matrix
            ? round(collect($this->matrix)->avg('persentase_hadir'), 1)
            : 0;

        $this->mahasiswaBerisiko = collect($this->matrix)
            ->filter(fn($row) => $row['persentase_hadir'] < $this->ambangBatasPersen)
            ->count();
        foreach ($this->mahasiswaList as $krsDetail) {
            $rowAbsensi = $absensis->get($krsDetail->id, collect())->keyBy('perkuliahan_sesi_id');

            $row = [];
            $rekapCount = collect(StatusKehadiranEnum::cases())
                ->mapWithKeys(fn($case) => [$case->value => 0])
                ->all();

            foreach ($this->sesiList as $sesi) {
                $statusEnum = $rowAbsensi->get($sesi->id)?->status_kehadiran
                    ?? StatusKehadiranEnum::ALPA;

                $row[$sesi->id] = $statusEnum;
                $rekapCount[$statusEnum->value]++;
            }

            $totalSesi = $this->sesiList->count();

            $this->matrix[$krsDetail->id] = [
                'mahasiswa' => $krsDetail->krs->mahasiswa,
                'sesi' => $row,
                'rekap' => $rekapCount,
                'persentase_hadir' => $totalSesi > 0
                    ? round($rekapCount[StatusKehadiranEnum::HADIR->value] / $totalSesi * 100, 1)
                    : 0,
            ];

            $this->rataRataKehadiran = $this->matrix
                ? round(collect($this->matrix)->avg('persentase_hadir'), 1)
                : 0;

            $this->mahasiswaBerisiko = collect($this->matrix)
                ->filter(fn($row) => $row['persentase_hadir'] < $this->ambangBatasPersen)
                ->count();
        }
    }

    public function cetakPdf()
    {
        // eager load biar accessor nama_dengan_gelar tidak N+1 query
        $dosen = auth()->user()?->person?->loadMissing('gelars');

        $pdf = Pdf::loadView('filament.dosen.resources.jadwal-mengajars.pages.rekap-kehadiran-pdf', [
            'record' => $this->record,
            'sesiList' => $this->sesiList,
            'matrix' => $this->matrix,
            'rataRataKehadiran' => $this->rataRataKehadiran,
            'ambangBatasPersen' => $this->ambangBatasPersen,
            'dosenNama' => $dosen?->nama_dengan_gelar ?? '-', // ganti dari nama_lengkap
            'tanggalCetak' => now()->translatedFormat('d F Y, H:i'),
        ])->setPaper('a4', 'landscape');

        $namaFile = 'rekap-kehadiran-'
            . Str::slug($this->record->mataKuliah->nama_mk ?? 'kelas')
            . '-' . Str::slug($this->record->kelas->nama_kelas ?? '')
            . '.pdf';

        return response()->streamDownload(fn() => print($pdf->output()), $namaFile);
    }
}
