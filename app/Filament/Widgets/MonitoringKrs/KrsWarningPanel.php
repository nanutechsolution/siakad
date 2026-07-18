<?php

namespace App\Filament\Widgets\MonitoringKrs;

use App\Enums\StatusKuliah;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\RefAturanSks;
use App\Models\RefTahunAkademik;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Cache;

class KrsWarningPanel extends Widget
{
    use InteractsWithPageFilters;

    protected  string $view = 'filament.widgets.krs-warning-panel';

    protected int|string|array $columnSpan = 'full';

    public function getWarnings(): array
    {
        $user = auth()->user();
        $taId = $this->pageFilters['tahun_akademik_id']
            ?? RefTahunAkademik::query()->where('is_active', true)->value('id');

        if (! $taId) {
            return [];
        }

        $ttl = now()->addMinutes((int) config('monitoring-krs.cache_ttl_minutes', 3));
        $cacheKey = "monitoring-krs:warnings:{$user->id}:{$taId}:" . md5(json_encode($this->pageFilters));

        return Cache::remember($cacheKey, $ttl, function () use ($user, $taId) {
            $scopedMahasiswa = fn () => Mahasiswa::query()
                ->whereHas('riwayatStatus', fn ($q) => $q
                    ->where('tahun_akademik_id', $taId)
                    ->where('status_kuliah', StatusKuliah::AKTIF->value))
                ->when($this->pageFilters['prodi_id'] ?? null, fn ($q, $v) => $q->where('prodi_id', $v));

            // 1. Mahasiswa belum KRS = tidak punya baris krs, atau masih DRAFT
            $sudahMengisiIds = Krs::query()
                ->where('tahun_akademik_id', $taId)
                ->whereIn('status_krs', ['DIAJUKAN', 'DISETUJUI', 'DITOLAK'])
                ->pluck('mahasiswa_id');

            $belumKrs = (clone $scopedMahasiswa())->whereNotIn('id', $sudahMengisiIds)->count();

            // 2. KRS belum disetujui dosen wali (menunggu approval)
            $menungguApproval = Krs::query()
                ->where('tahun_akademik_id', $taId)
                ->where('status_krs', 'DIAJUKAN')
                ->when($this->pageFilters['prodi_id'] ?? null, fn ($q, $v) => $q
                    ->whereHas('mahasiswa', fn ($qq) => $qq->where('prodi_id', $v)))
                ->count();

            // 3. Mahasiswa mengambil SKS melebihi batas (berdasarkan ref_aturan_sks vs IPS semester lalu)
            $aturanSks = RefAturanSks::query()->orderBy('min_ips')->get();

            $melebihiBatas = Krs::query()
                ->where('tahun_akademik_id', $taId)
                ->whereIn('status_krs', ['DIAJUKAN', 'DISETUJUI'])
                ->with(['mahasiswa.riwayatStatus' => fn ($q) => $q->where('tahun_akademik_id', '<', $taId)->latest('tahun_akademik_id')->limit(1)])
                ->get()
                ->filter(function (Krs $krs) use ($aturanSks) {
                    $ips = optional($krs->mahasiswa->riwayatStatus->first())->ips ?? 0;

                    $maxSks = $aturanSks->first(fn ($r) => $ips >= $r->min_ips && $ips <= $r->max_ips)?->max_sks ?? 24;

                    return $krs->total_sks_diambil > $maxSks;
                })
                ->count();

            // 4. Mahasiswa tanpa dosen wali: krs.dosen_wali_id null DAN kelas tidak punya dosen wali
            $tanpaDosenWali = (clone $scopedMahasiswa())
                ->whereDoesntHave('krs', fn ($q) => $q->where('tahun_akademik_id', $taId)->whereNotNull('dosen_wali_id'))
                ->whereDoesntHave('kelasAktif')
                ->count();

            return [
                [
                    'label' => 'Mahasiswa belum mengisi KRS',
                    'total' => $belumKrs,
                    'color' => 'danger',
                    'icon' => 'heroicon-o-exclamation-triangle',
                ],
                [
                    'label' => 'KRS menunggu approval dosen wali',
                    'total' => $menungguApproval,
                    'color' => 'warning',
                    'icon' => 'heroicon-o-clock',
                ],
                [
                    'label' => 'Mahasiswa mengambil SKS melebihi batas',
                    'total' => $melebihiBatas,
                    'color' => 'danger',
                    'icon' => 'heroicon-o-scale',
                ],
                [
                    'label' => 'Mahasiswa tanpa dosen wali',
                    'total' => $tanpaDosenWali,
                    'color' => 'gray',
                    'icon' => 'heroicon-o-user-minus',
                ],
            ];
        });
    }
}