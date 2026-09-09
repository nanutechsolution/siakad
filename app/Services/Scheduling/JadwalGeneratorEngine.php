<?php

namespace App\Services\Scheduling;

use App\Models\JadwalGeneratorBatch;
use App\Models\JadwalGeneratorResult;
use App\Models\RefRuang;
use Illuminate\Support\Facades\Cache;

class JadwalGeneratorEngine
{
    protected JadwalGeneratorBatch $batch;
    protected array $ruangTersedia;
    protected array $hariOperasional;
    protected array $slotWaktu;
    protected string $modeWaktu;
    protected int $menitPerSks;
    protected array $jamIstirahat;

    public function __construct(JadwalGeneratorBatch $batch)
    {
        $this->batch = $batch;

        $config = $batch->config_snapshot;
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $this->modeWaktu = $config['mode_waktu'] ?? 'dinamis';
        $this->menitPerSks = (int) ($config['menit_per_sks'] ?? 45);
        $this->jamIstirahat = $config['jam_istirahat'] ?? [['mulai' => '12:00', 'selesai' => '13:00']];
        $this->hariOperasional = !empty($config['hari']) ? $config['hari'] : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $this->slotWaktu = !empty($config['slots']) ? $config['slots'] : [
            ['mulai' => '08:00', 'selesai' => '09:30'],
            ['mulai' => '09:30', 'selesai' => '11:00'],
            ['mulai' => '11:00', 'selesai' => '12:30'],
            ['mulai' => '13:00', 'selesai' => '14:30'],
            ['mulai' => '14:30', 'selesai' => '16:00'],
        ];

        $ruangQuery = RefRuang::where('is_active', 1)->orderBy('kapasitas', 'asc');
        if ($this->batch->kampus_id) {
            $ruangQuery->where('kampus_id', $this->batch->kampus_id);
        }
        $this->ruangTersedia = $ruangQuery->get()->toArray();
    }

    public function execute(): void
    {
        if (empty($this->ruangTersedia)) {
            $this->batch->update([
                'status' => 'PREVIEW',
                'total_generated' => 0,
                'total_failed' => 0,
                'failure_reason' => 'CRITICAL: Tidak ada ruangan aktif yang ditemukan untuk kampus ini.',
            ]);
            return;
        }

        // --- Lock per (tahun_akademik_id, kampus_id): mencegah dua batch di
        // kampus+TA yang sama diproses worker berbeda secara bersamaan, yang
        // akan membuat masing-masing bekerja dari snapshot tracker yang basi
        // (perbaikan bug B6 -- race condition antar batch). ---
        $lockKey = "jadwal-generator:{$this->batch->tahun_akademik_id}:{$this->batch->kampus_id}";
        $lock = Cache::lock($lockKey, 900); // selaras dgn timeout job (15 menit)

        if (!$lock->get()) {
            $this->batch->update([
                'status' => 'FAILED',
                'failure_reason' => 'Kampus dan tahun akademik ini sedang diproses oleh batch lain. Coba lagi setelah batch tersebut selesai.',
            ]);
            return;
        }

        try {
            $loader = new ConstraintContextLoader();
            $context = $loader->load($this->batch);
            $tracker = $context['tracker'];

            $collector = new DemandCollector($this->ruangTersedia);
            $demandItems = $collector->collect($this->batch);
            $preFailures = $collector->getPreFailures();

            $generator = new CandidateGenerator(
                $this->hariOperasional,
                $this->slotWaktu,
                $this->jamIstirahat,
                $this->modeWaktu,
                $this->menitPerSks,
                $this->ruangTersedia,
                $context['limitasiWaktuDosen'],
            );
            $slotMulaiList = array_column($this->slotWaktu, 'mulai');
            $scorer = new CandidateScorer($this->hariOperasional, $slotMulaiList);

            $constructive = new GreedyConstructiveScheduler($generator, $scorer);
            $hasil = $constructive->run($demandItems, $tracker);

            $optimizer = new LocalSearchOptimizer($generator, $scorer);
            $assigned = $optimizer->optimize($hasil['assigned'], $tracker);

            $this->simpanHasil($assigned, $hasil['failed'], $preFailures);

            $qualityScorer = new QualityScorer();
            $ringkasan = $qualityScorer->ringkasanBatch($assigned, array_merge($hasil['failed'], $preFailures));

            $this->batch->update([
                'status' => 'PREVIEW',
                'total_generated' => count($assigned),
                'total_failed' => count($hasil['failed']) + count($preFailures),
                'quality_score' => $ringkasan['quality_score'],
                'quality_summary' => $ringkasan['quality_summary'],
            ]);
        } finally {
            $lock->release();
        }
    }

    protected function simpanHasil(array $assigned, array $failed, array $preFailures): void
    {
        $skorMentahMaks = empty($assigned) ? 0.0 : max(array_map(fn($a) => $a['candidate']->skor, $assigned));
        $qualityScorer = new QualityScorer();

        foreach ($assigned as $entry) {
            $item = $entry['item'];
            $c = $entry['candidate'];

            JadwalGeneratorResult::create([
                'batch_id' => $this->batch->id,
                'mata_kuliah_id' => $item->mataKuliahId,
                'kelas_id' => $item->kelasId,
                'dosen_pengampu_ids' => $item->dosenPengampuRowIds,
                'sks_real' => $item->sksTotal,
                'estimasi_kapasitas_dibutuhkan' => $item->kapasitasDibutuhkan,
                'is_success' => true,
                'hari' => $c->hari,
                'jam_mulai' => $c->jamMulai,
                'jam_selesai' => $c->jamSelesai,
                'ruang_id' => $c->ruangId,
                'optimization_score' => $qualityScorer->skorAssignment($c->skor, $skorMentahMaks),
            ]);
        }

        foreach ($failed as $entry) {
            $item = $entry['item'];
            JadwalGeneratorResult::create([
                'batch_id' => $this->batch->id,
                'mata_kuliah_id' => $item->mataKuliahId,
                'kelas_id' => $item->kelasId,
                'dosen_pengampu_ids' => $item->dosenPengampuRowIds,
                'sks_real' => $item->sksTotal,
                'estimasi_kapasitas_dibutuhkan' => $item->kapasitasDibutuhkan,
                'is_success' => false,
                'failure_reason' => $entry['reason'],
            ]);
        }

        foreach ($preFailures as $pf) {
            JadwalGeneratorResult::create([
                'batch_id' => $this->batch->id,
                'mata_kuliah_id' => $pf['mata_kuliah_id'],
                'kelas_id' => $pf['kelas_id'],
                'dosen_pengampu_ids' => $pf['dosen_pengampu_ids'],
                'sks_real' => $pf['sks_real'],
                'estimasi_kapasitas_dibutuhkan' => $pf['estimasi_kapasitas_dibutuhkan'],
                'is_success' => false,
                'failure_reason' => $pf['reason'],
            ]);
        }
    }
}
