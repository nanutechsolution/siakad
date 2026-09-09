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
use Illuminate\Support\Facades\Log;

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
        $kapasitasDibutuhkan = MahasiswaKelas::query()
            ->where('mahasiswa_kelas.kelas_id', $kelasId)
            ->whereNull('mahasiswa_kelas.tanggal_keluar')
            ->join(
                'mahasiswas',
                'mahasiswas.id',
                '=',
                'mahasiswa_kelas.mahasiswa_id'
            )
            ->where('mahasiswas.prodi_id', $kelasProdiId)
            ->count();

        $kapasitasDibutuhkan = $kapasitasDibutuhkan > 0
            ? $kapasitasDibutuhkan
            : ($firstItem->kelas->kapasitas ?? 40);
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

        $sksTatapMuka = (int) $kurikulumMK->sks_tatap_muka;
        $sksPraktek = (int) $kurikulumMK->sks_praktek;
        $sksLapangan = (int) $kurikulumMK->sks_lapangan;

        $sksTotal = $sksTatapMuka + $sksPraktek + $sksLapangan;

        /*
|--------------------------------------------------------------------------
| RUANG
|--------------------------------------------------------------------------
| Jika admin sudah memilih ruang pada dosen_pengampus,
| pilihan tersebut menjadi constraint utama.
*/
        if ($reqRuangId) {

            // Cari ruang yang dipilih admin dari ruang yang tersedia di batch
            $ruangTetap = collect($this->ruangTersedia)
                ->firstWhere('id', $reqRuangId);
            if (
                $ruangTetap &&
                !is_null($ruangTetap['prodi_id']) &&
                (int) $ruangTetap['prodi_id'] !== (int) $kelasProdiId
            ) {
                $this->preFailures[] = [
                    'mata_kuliah_id' => $mkId,
                    'kelas_id' => $kelasId,
                    'reason' => "CRITICAL: Ruang '{$ruangTetap['nama_ruang']}' merupakan ruang eksklusif prodi lain dan tidak boleh digunakan oleh prodi ini.",
                    'dosen_pengampu_ids' => $dosenList->pluck('id')->all(),
                    'sks_real' => $sksTotal,
                    'estimasi_kapasitas_dibutuhkan' => $kapasitasDibutuhkan,
                ];

                return null;
            }

            /*
     * ADMIN SUDAH MEMILIH RUANG
     *
     * Jangan override dengan sks_praktek.
     * Ruang pilihan admin dihormati.
     */
            $jenisRuangDibutuhkan = $ruangTetap['jenis_ruang'];

            // Untuk fixed room, ruang yang digunakan hanya ruang tersebut.
            $ruangSesuaiJenis = collect([$ruangTetap]);
        } else {

            /*
     * ADMIN BELUM MEMILIH RUANG
     *
     * Generator menentukan ruang berdasarkan komposisi SKS.
     */
            $jenisRuangDibutuhkan = $sksPraktek > $sksTatapMuka
                ? 'LABORATORIUM'
                : 'TEORI';

            $ruangSesuaiJenis = collect($this->ruangTersedia)
                ->where('jenis_ruang', $jenisRuangDibutuhkan);
        }

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

        /*
|--------------------------------------------------------------------------
| RUANG YANG BOLEH DIPAKAI
|--------------------------------------------------------------------------
| Fixed room:
|   → pilihan admin dihormati.
|
| Automatic room:
|   → tetap filter berdasarkan prodi-exclusive / ruang umum.
*/
        if ($reqRuangId) {
            // Admin sudah menentukan ruang → hormati pilihan admin
            $ruangBolehDipakai = $ruangSesuaiJenis;
        } else {
            // Auto → hanya ruang umum atau ruang milik prodi
            $ruangBolehDipakai = $ruangSesuaiJenis->filter(
                fn($r) =>
                is_null($r['prodi_id'])
                    || $r['prodi_id'] == $kelasProdiId
            );
        }

        // Jika auto dan tidak ada ruang yang boleh digunakan
        if (!$reqRuangId && $ruangBolehDipakai->isEmpty()) {
            $this->preFailures[] = [
                'mata_kuliah_id' => $mkId,
                'kelas_id' => $kelasId,
                'reason' => "CRITICAL: Tidak ada ruang {$jenisRuangDibutuhkan} yang boleh digunakan prodi ini.",
                'dosen_pengampu_ids' => $dosenList->pluck('id')->all(),
                'sks_real' => $sksTotal,
                'estimasi_kapasitas_dibutuhkan' => $kapasitasDibutuhkan,
            ];

            return null;
        }


        /*
|--------------------------------------------------------------------------
| VALIDASI KAPASITAS RUANG
|--------------------------------------------------------------------------
*/
        if ($reqRuangId) {

            // Ruang sudah dipilih admin → wajib dicek kapasitasnya
            $kapasitasRuang = (int) (
                $ruangBolehDipakai->first()['kapasitas'] ?? 0
            );

            if ($kapasitasRuang < $kapasitasDibutuhkan) {
                $this->preFailures[] = [
                    'mata_kuliah_id' => $mkId,
                    'kelas_id' => $kelasId,
                    'reason' => "CRITICAL: Ruang yang dipilih admin hanya memiliki {$kapasitasRuang} kursi, sedangkan kelas membutuhkan {$kapasitasDibutuhkan} kursi.",
                    'dosen_pengampu_ids' => $dosenList->pluck('id')->all(),
                    'sks_real' => $sksTotal,
                    'estimasi_kapasitas_dibutuhkan' => $kapasitasDibutuhkan,
                ];

                return null;
            }
        } else {

            // Auto → cari kapasitas terbesar dari ruang yang diperbolehkan
            $maxKapasitasTersedia = $ruangBolehDipakai->max('kapasitas');

            if ($kapasitasDibutuhkan > $maxKapasitasTersedia) {
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
        $kurikulumIds = MahasiswaKelas::query()
            ->where('mahasiswa_kelas.kelas_id', $kelas->id)
            ->whereNull('mahasiswa_kelas.tanggal_keluar')
            ->join(
                'mahasiswas',
                'mahasiswas.id',
                '=',
                'mahasiswa_kelas.mahasiswa_id'
            )
            ->where('mahasiswas.prodi_id', $kelas->prodi_id)
            ->whereNotNull('mahasiswas.kurikulum_id')
            ->pluck('mahasiswas.kurikulum_id')
            ->unique();

        if ($kurikulumIds->count() > 1) {
            $this->preFailures[] = [
                'mata_kuliah_id' => $mataKuliahId,
                'kelas_id' => $kelas->id,
                'reason' => 'Mahasiswa dalam kelas memiliki lebih dari satu kurikulum aktif.',
                'dosen_pengampu_ids' => [],
                'sks_real' => 0,
                'estimasi_kapasitas_dibutuhkan' => 0,
            ];

            Log::warning('Kelas memiliki multiple kurikulum', [
                'kelas_id' => $kelas->id,
                'prodi_id' => $kelas->prodi_id,
                'kurikulum_ids' => $kurikulumIds->values()->all(),
            ]);

            return null;
        }

        $kurikulumId = $kurikulumIds->first();

        if (!$kurikulumId) {
            return null;
        }

        return KurikulumMataKuliah::query()
            ->where('kurikulum_id', $kurikulumId)
            ->where('mata_kuliah_id', $mataKuliahId)
            ->first();
    }
}
