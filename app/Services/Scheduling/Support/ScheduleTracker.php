<?php

namespace App\Services\Scheduling\Support;

/**
 * Menyimpan seluruh reservasi waktu yang sudah "terkunci" -- baik dari jadwal
 * production (JadwalKuliah) maupun dari batch preview lain yang masih aktif
 * (JadwalGeneratorResult dengan batch berstatus PREVIEW/RUNNING).
 *
 * Dipakai bersama oleh ConstraintContextLoader (pengisi awal),
 * CandidateGenerator (pengecek hard constraint), dan
 * GreedyConstructiveScheduler/LocalSearchOptimizer (penambah reservasi baru
 * saat assignment terpilih).
 *
 * Sengaja TIDAK membedakan sumber reservasi (production vs preview aktif) --
 * begitu masuk ke sini, keduanya sama-sama dianggap "sudah dipakai" untuk
 * keperluan pengecekan bentrok. Ini yang membuat generate-per-prodi tetap
 * global-aware (lihat poin K rancangan).
 */
class ScheduleTracker
{
    /** @var array<int|string, array<string, array{mulai:string, selesai:string}[]>> */
    protected array $byDosen = [];

    /** @var array<int, array<string, array{mulai:string, selesai:string}[]>> */
    protected array $byKelas = [];

    /** @var array<int, array<string, array{mulai:string, selesai:string}[]>> */
    protected array $byRuang = [];

    /**
     * Hari-hari yang harus "dikarantina" penuh untuk seorang dosen, karena
     * dosen tsb sudah mengajar di kampus lain pada hari itu.
     * @var array<int|string, string[]>
     */
    protected array $karantinaHariDosen = [];

    /**
     * Statistik beban untuk keperluan scoring soft constraint (poin G):
     * jumlah sesi per hari (global), per slot, per dosen per hari, per ruang,
     * dan per prodi (untuk fairness antar prodi).
     */
    protected array $bebanPerHari = [];
    protected array $bebanPerSlotKey = [];
    protected array $bebanDosenPerHari = [];
    protected array $bebanRuang = [];
    protected array $bebanProdiPerHari = [];

    public function reserve(
        array $dosenIds,
        int $kelasId,
        int $ruangId,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        ?int $prodiId = null
    ): void {
        $rentang = ['mulai' => $jamMulai, 'selesai' => $jamSelesai];

        $this->byKelas[$kelasId][$hari][] = $rentang;
        $this->byRuang[$ruangId][$hari][] = $rentang;
        foreach ($dosenIds as $dId) {
            $this->byDosen[$dId][$hari][] = $rentang;
            $this->bebanDosenPerHari[$dId][$hari] = ($this->bebanDosenPerHari[$dId][$hari] ?? 0) + 1;
        }

        $this->bebanPerHari[$hari] = ($this->bebanPerHari[$hari] ?? 0) + 1;
        $slotKey = $hari . '|' . $jamMulai;
        $this->bebanPerSlotKey[$slotKey] = ($this->bebanPerSlotKey[$slotKey] ?? 0) + 1;
        $this->bebanRuang[$ruangId] = ($this->bebanRuang[$ruangId] ?? 0) + 1;

        if ($prodiId !== null) {
            $this->bebanProdiPerHari[$prodiId][$hari] = ($this->bebanProdiPerHari[$prodiId][$hari] ?? 0) + 1;
        }
    }

    /**
     * Melepas satu reservasi yang sebelumnya dibuat via reserve() dengan
     * parameter identik. Dipakai LocalSearchOptimizer untuk mencoba
     * merelokasi sebuah assignment ke slot lain secara sementara.
     */
    public function unreserve(
        array $dosenIds,
        int $kelasId,
        int $ruangId,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        ?int $prodiId = null
    ): void {
        $this->byKelas[$kelasId][$hari] = $this->hapusSatuRentang($this->byKelas[$kelasId][$hari] ?? [], $jamMulai, $jamSelesai);
        $this->byRuang[$ruangId][$hari] = $this->hapusSatuRentang($this->byRuang[$ruangId][$hari] ?? [], $jamMulai, $jamSelesai);

        foreach ($dosenIds as $dId) {
            $this->byDosen[$dId][$hari] = $this->hapusSatuRentang($this->byDosen[$dId][$hari] ?? [], $jamMulai, $jamSelesai);
            if (isset($this->bebanDosenPerHari[$dId][$hari])) {
                $this->bebanDosenPerHari[$dId][$hari] = max(0, $this->bebanDosenPerHari[$dId][$hari] - 1);
            }
        }

        if (isset($this->bebanPerHari[$hari])) {
            $this->bebanPerHari[$hari] = max(0, $this->bebanPerHari[$hari] - 1);
        }
        $slotKey = $hari . '|' . $jamMulai;
        if (isset($this->bebanPerSlotKey[$slotKey])) {
            $this->bebanPerSlotKey[$slotKey] = max(0, $this->bebanPerSlotKey[$slotKey] - 1);
        }
        if (isset($this->bebanRuang[$ruangId])) {
            $this->bebanRuang[$ruangId] = max(0, $this->bebanRuang[$ruangId] - 1);
        }
        if ($prodiId !== null && isset($this->bebanProdiPerHari[$prodiId][$hari])) {
            $this->bebanProdiPerHari[$prodiId][$hari] = max(0, $this->bebanProdiPerHari[$prodiId][$hari] - 1);
        }
    }

    /** Hapus satu entri rentang waktu (mulai+selesai persis sama) dari daftar, hanya kemunculan pertama. */
    protected function hapusSatuRentang(array $list, string $mulai, string $selesai): array
    {
        $sudahDihapus = false;
        return array_values(array_filter($list, function ($rentang) use ($mulai, $selesai, &$sudahDihapus) {
            if (!$sudahDihapus && $rentang['mulai'] === $mulai && $rentang['selesai'] === $selesai) {
                $sudahDihapus = true;
                return false;
            }
            return true;
        }));
    }

    public function markKarantina(int|string $dosenId, string $hari): void
    {
        $this->karantinaHariDosen[$dosenId][] = $hari;
    }

    public function isDosenKarantinaDiHari(int|string $dosenId, string $hari): bool
    {
        return isset($this->karantinaHariDosen[$dosenId])
            && in_array($hari, $this->karantinaHariDosen[$dosenId], true);
    }

    public function isDosenBentrok(int|string $dosenId, string $hari, string $mulai, string $selesai): bool
    {
        return $this->overlap($this->byDosen, $dosenId, $hari, $mulai, $selesai);
    }

    public function isKelasBentrok(int $kelasId, string $hari, string $mulai, string $selesai): bool
    {
        return $this->overlap($this->byKelas, $kelasId, $hari, $mulai, $selesai);
    }

    public function isRuangBentrok(int $ruangId, string $hari, string $mulai, string $selesai): bool
    {
        return $this->overlap($this->byRuang, $ruangId, $hari, $mulai, $selesai);
    }

    protected function overlap(array $tracker, int|string $id, string $hari, string $mulaiTarget, string $selesaiTarget): bool
    {
        if (!isset($tracker[$id][$hari])) {
            return false;
        }
        foreach ($tracker[$id][$hari] as $booked) {
            if ($mulaiTarget < $booked['selesai'] && $selesaiTarget > $booked['mulai']) {
                return true;
            }
        }
        return false;
    }

    // --- Getter untuk CandidateScorer (poin G) ---

    public function bebanHari(string $hari): int
    {
        return $this->bebanPerHari[$hari] ?? 0;
    }

    public function rataRataBebanHari(array $semuaHari): float
    {
        if (empty($semuaHari)) {
            return 0.0;
        }
        $total = array_sum(array_map(fn ($h) => $this->bebanHari($h), $semuaHari));
        return $total / count($semuaHari);
    }

    public function bebanSlot(string $hari, string $jamMulai): int
    {
        return $this->bebanPerSlotKey[$hari . '|' . $jamMulai] ?? 0;
    }

    public function rataRataBebanSlot(array $semuaHari, array $semuaSlotMulai): float
    {
        $keys = [];
        foreach ($semuaHari as $h) {
            foreach ($semuaSlotMulai as $s) {
                $keys[] = $this->bebanSlot($h, $s);
            }
        }
        return empty($keys) ? 0.0 : array_sum($keys) / count($keys);
    }

    public function bebanDosenHari(int|string $dosenId, string $hari): int
    {
        return $this->bebanDosenPerHari[$dosenId][$hari] ?? 0;
    }

    public function rataRataBebanDosen(int|string $dosenId, array $semuaHari): float
    {
        $total = array_sum(array_map(fn ($h) => $this->bebanDosenHari($dosenId, $h), $semuaHari));
        return empty($semuaHari) ? 0.0 : $total / count($semuaHari);
    }

    public function bebanRuang(int $ruangId): int
    {
        return $this->bebanRuang[$ruangId] ?? 0;
    }

    public function rataRataBebanRuang(array $semuaRuangId): float
    {
        if (empty($semuaRuangId)) {
            return 0.0;
        }
        $total = array_sum(array_map(fn ($r) => $this->bebanRuang($r), $semuaRuangId));
        return $total / count($semuaRuangId);
    }

    public function bebanProdiHari(int $prodiId, string $hari): int
    {
        return $this->bebanProdiPerHari[$prodiId][$hari] ?? 0;
    }
}
