<?php

namespace App\Filament\Widgets\MonitoringKrs;

use App\Enums\KrsStatusEnum;
use App\Enums\StatusKuliah;
use App\Enums\StatusKuliahEnum;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class KrsStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $taId = $this->pageFilters['tahun_akademik_id']
            ?? RefTahunAkademik::query()->where('is_active', true)->value('id');

        if (! $taId) {
            return [
                Stat::make('Tahun Akademik', 'Belum ada TA aktif')
                    ->description('Aktifkan Tahun Akademik terlebih dahulu')
                    ->color('gray'),
            ];
        }

        $data = $this->computeStats((int) $taId);

        $persen = $data['wajib_krs'] > 0
            ? round($data['sudah_mengisi'] / $data['wajib_krs'] * 100, 1)
            : 0.0;

        return [
            Stat::make('Mahasiswa Aktif', number_format($data['mahasiswa_aktif']))
                ->description('Berstatus aktif pada TA terpilih')
                ->color('gray'),

            Stat::make('Wajib Mengisi KRS', number_format($data['wajib_krs']))
                ->color('gray'),

            Stat::make('Progress KRS', "{$persen}%")
                ->description(number_format($data['sudah_mengisi']) . ' / ' . number_format($data['wajib_krs']))
                ->color($persen >= 80 ? 'success' : ($persen >= 50 ? 'warning' : 'danger')),

            Stat::make('Belum Mengisi KRS', number_format($data['belum_mengisi']))
                ->color('danger'),

            Stat::make('Menunggu Approval', number_format($data['diajukan']))
                ->description('Status: Diajukan')
                ->color('warning'),

            Stat::make('Sudah Disetujui', number_format($data['disetujui']))
                ->color('success'),

            Stat::make('Ditolak Dosen Wali', number_format($data['ditolak']))
                ->color('danger'),

            Stat::make('% Penyelesaian', "{$persen}%")
                ->description('Diajukan + Disetujui + Ditolak / Wajib KRS')
                ->color($persen >= 80 ? 'success' : ($persen >= 50 ? 'warning' : 'danger')),
        ];
    }

    private function computeStats(int $taId): array
    {
        $user = auth()->user();
        $ttl = now()->addMinutes((int) config('monitoring-krs.cache_ttl_minutes', 3));
        $cacheKey = "monitoring-krs:stats:{$user->id}:{$taId}:" . md5(json_encode($this->pageFilters));

        return Cache::remember($cacheKey, $ttl, function () use ($user, $taId) {
            $baseMahasiswa = Mahasiswa::query()
                ->whereHas('riwayatStatus', fn($q) => $q
                    ->where('tahun_akademik_id', $taId)
                    ->where('status_kuliah', StatusKuliah::AKTIF->value))
                ->when($this->pageFilters['prodi_id'] ?? null, fn($q, $v) => $q->where('prodi_id', $v))
                ->when($this->pageFilters['fakultas_id'] ?? null, fn($q, $v) => $q
                    ->whereHas('prodi', fn($qq) => $qq->where('fakultas_id', $v)))
                ->when($this->pageFilters['angkatan_id'] ?? null, fn($q, $v) => $q->where('angkatan_id', $v));

            $mahasiswaAktif = (clone $baseMahasiswa)->count();
            $wajibKrs = $mahasiswaAktif; // asumsi: semua mahasiswa Aktif wajib isi KRS

            $mahasiswaIds = (clone $baseMahasiswa)->pluck('id');

            $statusCounts = Krs::query()
                ->where('tahun_akademik_id', $taId)
                ->whereIn('mahasiswa_id', $mahasiswaIds)
                ->selectRaw('status_krs, COUNT(*) as total')
                ->groupBy('status_krs')
                ->pluck('total', 'status_krs');

            $sudahMengisi = 0;
            foreach (KrsStatusEnum::sudahMengisiValues() as $status) {
                $sudahMengisi += (int) ($statusCounts[$status] ?? 0);
            }

            return [
                'mahasiswa_aktif' => $mahasiswaAktif,
                'wajib_krs' => $wajibKrs,
                'sudah_mengisi' => $sudahMengisi,
                'belum_mengisi' => max($wajibKrs - $sudahMengisi, 0),
                'diajukan' => (int) ($statusCounts[KrsStatusEnum::DIAJUKAN->value] ?? 0),
                'disetujui' => (int) ($statusCounts[KrsStatusEnum::DISETUJUI->value] ?? 0),
                'ditolak' => (int) ($statusCounts[KrsStatusEnum::DITOLAK->value] ?? 0),
            ];
        });
    }
}
