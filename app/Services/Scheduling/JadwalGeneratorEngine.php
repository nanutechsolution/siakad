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
    protected array $jamOperasional;
    protected string $modeWaktu;
    protected int $menitPerSks;
    protected int $menitTransisi;
    protected array $jamIstirahat;
    protected int $kampusUtamaId;
    public function __construct(JadwalGeneratorBatch $batch)
    {
        $this->batch = $batch;

        $config = $batch->config_snapshot;
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $this->modeWaktu = $config['mode_waktu'] ?? 'dinamis';
        $this->menitPerSks = (int) ($config['menit_per_sks'] ?? 45);

        // 1. PERBAIKAN: Paksa transisi jadi 0 jika mode statis
        $this->menitTransisi = $this->modeWaktu === 'statis' ? 0 : (int) ($config['menit_transisi'] ?? 10);

        $this->jamIstirahat = $config['jam_istirahat'] ?? [];

        // --- PARSING DATA HARI & JAM OPERASIONAL (STRUKTUR BARU) ---
        $rawHari = $config['hari'] ?? [];
        $this->hariOperasional = [];
        $this->jamOperasional = [];

        foreach ($rawHari as $day => $settings) {
            // Cek apakah hari tersebut dicentang 'aktif' di form
            if (isset($settings['aktif']) && $settings['aktif'] === true) {
                $this->hariOperasional[] = $day;
                $this->jamOperasional[$day] = [
                    'mulai' => $settings['mulai'] ?? '08:00',
                    'selesai' => $settings['selesai'] ?? '16:00',
                    'slots' => $settings['slots'] ?? [],
                ];
            }
        }

        // --- FILTER RUANG ---
        $ruangQuery = RefRuang::query()
            ->where('is_active', 1)
            ->orderBy('kapasitas', 'asc');
        $this->ruangTersedia = $ruangQuery
            ->get([
                'id',
                'kampus_id',
                'nama_ruang',
                'jenis_ruang',
                'kapasitas',
                'prodi_id',
                'is_active',
            ])
            ->toArray();
        // if ($this->batch->kampus_id) {
        //     $ruangQuery->where('kampus_id', $this->batch->kampus_id);
        // }
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

        $lockKey = "jadwal-generator:{$this->batch->tahun_akademik_id}:{$this->batch->kampus_id}";
        $lock = Cache::lock($lockKey, 900);

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
            $collector = new DemandCollector(
                $this->ruangTersedia,
                $this->kampusUtamaId,
            );
            $demandItems = $collector->collect($this->batch);
            $preFailures = $collector->getPreFailures();
            $generator = new CandidateGenerator(
                $this->hariOperasional,
                $this->jamOperasional, // Menggantikan $this->slotWaktu
                $this->jamIstirahat,
                $this->modeWaktu,
                $this->menitPerSks,
                $this->menitTransisi, // Tambahan parameter baru
                $this->ruangTersedia,
                $context['limitasiWaktuDosen']
            );

            // Karena tidak ada lagi global slot, parameter Scorer mungkin butuh penyesuaian
            $scorer = new CandidateScorer($this->hariOperasional, $this->jamOperasional);

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
        $skorMentahMaks = empty($assigned)
            ? 0.0
            : max(array_map(
                fn($a) => $a['candidate']->skor,
                $assigned
            ));

        $qualityScorer = new QualityScorer();

        /*
     * ============================================================
     * HASIL BERHASIL
     * ============================================================
     */
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
                'status' => 'success',
                'failure_code' => null,

                /*
             * roomNote digunakan sebagai keterangan hasil.
             *
             * Contoh:
             * "Ruang pilihan admin 'R-101' penuh pada slot ini.
             *  Sistem otomatis memilih ruang alternatif."
             */
                'failure_reason' => $c->roomNote,

                'hari' => $c->hari,
                'jam_mulai' => $c->jamMulai,
                'jam_selesai' => $c->jamSelesai,
                'ruang_id' => $c->ruangId,

                'optimization_score' => $qualityScorer->skorAssignment(
                    $c->skor,
                    $skorMentahMaks
                ),
            ]);
        }

        /*
     * ============================================================
     * HASIL GAGAL / PERLU PENYESUAIAN
     * ============================================================
     */
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
                'status' => $entry['status'] ?? 'needs_adjustment',
                'failure_code' => $entry['failure_code'] ?? null,
                'failure_reason' => $entry['reason'] ?? null,

                'hari' => null,
                'jam_mulai' => null,
                'jam_selesai' => null,
                'ruang_id' => null,
                'optimization_score' => 0,
            ]);
        }

        /*
     * ============================================================
     * PRE-FAILURE / MASTER FAILURE
     * ============================================================
     */
        foreach ($preFailures as $pf) {
            JadwalGeneratorResult::create([
                'batch_id' => $this->batch->id,
                'mata_kuliah_id' => $pf['mata_kuliah_id'],
                'kelas_id' => $pf['kelas_id'],
                'dosen_pengampu_ids' => $pf['dosen_pengampu_ids'],
                'sks_real' => $pf['sks_real'],
                'estimasi_kapasitas_dibutuhkan' => $pf['estimasi_kapasitas_dibutuhkan'],

                'is_success' => false,
                'status' => 'master_failure',
                'failure_code' => $pf['failure_code'] ?? null,
                'failure_reason' => $pf['reason'] ?? null,

                'hari' => null,
                'jam_mulai' => null,
                'jam_selesai' => null,
                'ruang_id' => null,
                'optimization_score' => 0,
            ]);
        }
    }
}
