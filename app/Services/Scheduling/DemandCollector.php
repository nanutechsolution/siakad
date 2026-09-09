<?php

namespace App\Services\Scheduling;

use App\Models\DosenPengampu;
use App\Models\JadwalGeneratorBatch;
use App\Models\JadwalKuliah;
use App\Models\KurikulumMataKuliah;
use App\Models\MahasiswaKelas;
use App\Models\MasterKurikulum;
use App\Models\RefRuang;
use App\Services\Scheduling\Support\DemandItem;

class DemandCollector
{
    /** @var array<int, array{mata_kuliah_id:int, kelas_id:int, reason:string, dosen_pengampu_ids:array, sks_real:int, estimasi_kapasitas_dibutuhkan:int}> */
    protected array $preFailures = [];

    public function __construct(
        protected array $ruangTersedia, // dari batch, sudah difilter kampus_id + is_active
    ) {}

    /** @return DemandItem[] */
    public function collect(JadwalGeneratorBatch $batch): array
    {
        $this->preFailures = [];

        $jadwalProduction = JadwalKuliah::where('tahun_akademik_id', $batch->tahun_akademik_id)
            ->get(['mata_kuliah_id', 'kelas_id']);
        $kombinasiSudahAda = $jadwalProduction
            ->map(fn($jp) => $jp->mata_kuliah_id . '-' . $jp->kelas_id)
            ->all();

        // --- PERBAIKAN BUG C: filter juga berdasarkan kampus_id kelas, bukan
        // hanya prodi_id. Prodi bisa punya kelas di beberapa kampus; kelas
        // yang kampus_id-nya belum diisi (NULL) sengaja TIDAK ikut tertarik
        // sampai datanya dilengkapi (fail-safe, lihat komentar migration). ---
        $pengampus = DosenPengampu::with(['kelas', 'mataKuliah'])
            ->where('tahun_akademik_id', $batch->tahun_akademik_id)
            ->whereHas('kelas', function ($query) use ($batch) {
                $query->where('prodi_id', $batch->prodi_id)
                    ->where('kampus_id', $batch->kampus_id);
            })
            ->get()
            ->filter(function ($item) use ($kombinasiSudahAda) {
                $key = $item->mata_kuliah_id . '-' . $item->kelas_id;
                return !in_array($key, $kombinasiSudahAda, true);
            })
            ->groupBy(fn($item) => $item->mata_kuliah_id . '-' . $item->kelas_id);

        $items = [];

        foreach ($pengampus as $dosenList) {
            $firstItem = $dosenList->first();
            $item = $this->buildDemandItem($firstItem, $dosenList);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        $this->urutkanMostConstrainedFirst($items);

        return $items;
    }

    /** @return array Pre-failure yang dicatat selama collect() -- item yang mustahil berhasil */
    public function getPreFailures(): array
    {
        return $this->preFailures;
    }

    protected function buildDemandItem($firstItem, $dosenList): ?DemandItem
    {
        $kelasId = $firstItem->kelas_id;
        $mkId = $firstItem->mata_kuliah_id;
        $dosenIds = $dosenList->pluck('dosen_id')->all();
        $kelasProdiId = $firstItem->kelas->prodi_id ?? 0;
        $reqRuangId = $firstItem->ruang_id ?? null;

        $kapasitasDibutuhkan = MahasiswaKelas::where('kelas_id', $kelasId)->whereNull('tanggal_keluar')->count();
        $kapasitasDibutuhkan = $kapasitasDibutuhkan > 0 ? $kapasitasDibutuhkan : ($firstItem->kelas->kapasitas ?? 40);

        $kurikulumMK = $this->getKurikulumMataKuliahForKelas($mkId, $firstItem->kelas);
        // item yang tidak punya definisi kurikulum langsung dicatat sebagai
        // pre-failure yang jelas, bukan dijadwalkan dengan durasi tebakan. ---
        if (!$kurikulumMK) {
            $this->preFailures[] = [
                'mata_kuliah_id' => $mkId,
                'kelas_id' => $kelasId,
                'reason' => 'CRITICAL: Tidak ditemukan kurikulum_mata_kuliah yang cocok untuk mata kuliah ini pada kurikulum prodi kelas tsb. SKS tidak bisa ditentukan.',
                'dosen_pengampu_ids' => $dosenList->pluck('id')->all(),
                'sks_real' => 0,
                'estimasi_kapasitas_dibutuhkan' => $kapasitasDibutuhkan,
            ];
            return null;
        }

        $jenisRuangDibutuhkan = $kurikulumMK->sks_praktek > 0 ? 'LABORATORIUM' : 'TEORI';
        $sksTotal = (int) $kurikulumMK->sks_tatap_muka + (int) $kurikulumMK->sks_praktek + (int) $kurikulumMK->sks_lapangan;

        $ruangSesuaiJenis = collect($this->ruangTersedia)->where('jenis_ruang', $jenisRuangDibutuhkan);

        if ($ruangSesuaiJenis->isEmpty()) {
            $this->preFailures[] = [
                'mata_kuliah_id' => $mkId,
                'kelas_id' => $kelasId,
                'reason' => "CRITICAL: Kampus ini tidak memiliki ruangan aktif berjenis {$jenisRuangDibutuhkan}.",
                'dosen_pengampu_ids' => $dosenList->pluck('id')->all(),
                'sks_real' => $sksTotal,
                'estimasi_kapasitas_dibutuhkan' => $kapasitasDibutuhkan,
            ];
            return null;
        }

        // Ruang yang boleh dipakai kelas ini (prodi-exclusive atau umum) --
        // dipakai utk precheck kapasitas yang konsisten dgn CandidateGenerator
        // (perbaikan bug B7: precheck lama tidak mempertimbangkan eksklusivitas prodi).
        $ruangBolehDipakai = $ruangSesuaiJenis->filter(
            fn($r) => is_null($r['prodi_id']) || $r['prodi_id'] == $kelasProdiId
        );

        if (!$reqRuangId && $ruangBolehDipakai->isEmpty()) {
            $this->preFailures[] = [
                'mata_kuliah_id' => $mkId,
                'kelas_id' => $kelasId,
                'reason' => "CRITICAL: Tidak ada ruang jenis {$jenisRuangDibutuhkan} yang boleh dipakai prodi ini di kampus ini (semua ruang sejenis eksklusif milik prodi lain).",
                'dosen_pengampu_ids' => $dosenList->pluck('id')->all(),
                'sks_real' => $sksTotal,
                'estimasi_kapasitas_dibutuhkan' => $kapasitasDibutuhkan,
            ];
            return null;
        }

        $maxKapasitasTersedia = $reqRuangId ? PHP_INT_MAX : $ruangBolehDipakai->max('kapasitas');
        if (!$reqRuangId && $kapasitasDibutuhkan > $maxKapasitasTersedia) {
            $this->preFailures[] = [
                'mata_kuliah_id' => $mkId,
                'kelas_id' => $kelasId,
                'reason' => "CRITICAL: Butuh {$kapasitasDibutuhkan} kursi, tapi ruang {$jenisRuangDibutuhkan} yang boleh dipakai prodi ini maksimal hanya {$maxKapasitasTersedia} kursi.",
                'dosen_pengampu_ids' => $dosenList->pluck('id')->all(),
                'sks_real' => $sksTotal,
                'estimasi_kapasitas_dibutuhkan' => $kapasitasDibutuhkan,
            ];
            return null;
        }

        return new DemandItem(
            mataKuliahId: $mkId,
            kelasId: $kelasId,
            kelasProdiId: $kelasProdiId,
            dosenPengampuRowIds: $dosenList->pluck('id')->all(),
            dosenIds: $dosenIds,
            kapasitasDibutuhkan: $kapasitasDibutuhkan,
            jenisRuangDibutuhkan: $jenisRuangDibutuhkan,
            sksTotal: $sksTotal,
            reqRuangId: $reqRuangId,
        );
    }

    /**
     * Most Constrained First / mendekati Degree of Saturation (poin F, G):
     * item yang punya lebih sedikit "ruang gerak" harus dijadwalkan lebih
     * dulu, supaya item yang fleksibel bisa mengalah belakangan. Heuristik
     * murah (bukan hitung permutasi penuh) berdasarkan:
     *  - fixed room (paling ketat, wajib duluan)
     *  - jenis ruang yg langka (rasio jumlah ruang cocok vs total ruang)
     *  - kapasitas besar (lebih sedikit ruang yg muat)
     *  - jumlah dosen dalam team teaching (lebih banyak dosen = lebih rawan bentrok)
     */
    protected function urutkanMostConstrainedFirst(array &$items): void
    {
        $totalRuang = max(count($this->ruangTersedia), 1);
        $jumlahRuangPerJenis = collect($this->ruangTersedia)->countBy('jenis_ruang');

        usort($items, function (DemandItem $a, DemandItem $b) use ($totalRuang, $jumlahRuangPerJenis) {
            return $this->skorKesulitan($b, $totalRuang, $jumlahRuangPerJenis)
                <=> $this->skorKesulitan($a, $totalRuang, $jumlahRuangPerJenis);
        });
    }

    protected function skorKesulitan(DemandItem $item, int $totalRuang, $jumlahRuangPerJenis): float
    {
        if ($item->reqRuangId) {
            return 1000.0; // fixed room selalu paling ketat, proses paling awal
        }

        $rasioRuangJenis = ($jumlahRuangPerJenis[$item->jenisRuangDibutuhkan] ?? 1) / $totalRuang;
        $skor = (1 - $rasioRuangJenis) * 10;                    // makin langka jenis ruangnya, makin sulit
        $skor += min($item->kapasitasDibutuhkan / 100, 5);       // kapasitas besar = lebih sulit
        $skor += count($item->dosenIds) * 1.5;                  // team teaching lebih rawan bentrok

        $item->skorKesulitan = $skor;
        return $skor;
    }

    protected function getKurikulumMataKuliahForKelas(int $mataKuliahId, $kelas): ?KurikulumMataKuliah
    {
        $tahunAngkatan = (int) $kelas->angkatan_id;

        $kurikulum = MasterKurikulum::query()
            ->where('prodi_id', $kelas->prodi_id)
            ->where('tahun_mulai', '<=', $tahunAngkatan)
            ->orderByDesc('tahun_mulai')
            ->first();

        if (!$kurikulum) {
            return null;
        }

        return KurikulumMataKuliah::query()
            ->where('kurikulum_id', $kurikulum->id)
            ->where('mata_kuliah_id', $mataKuliahId)
            ->first();
    }
}
