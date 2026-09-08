<?php

namespace App\Services\Scheduling;

use App\Models\DosenPengampu;
use App\Models\JadwalGeneratorBatch;
use App\Models\JadwalGeneratorResult;
use App\Models\RefRuang;
use App\Models\JadwalKuliah;
use App\Models\MahasiswaKelas;
use App\Models\KurikulumMataKuliah;
use Carbon\Carbon;

class JadwalGeneratorEngine
{
    protected JadwalGeneratorBatch $batch;
    protected array $ruangTersedia;
    protected array $hariOperasional;
    protected array $slotWaktu;

    protected string $modeWaktu;
    protected int $menitPerSks;

    protected array $trackerDosen = [];
    protected array $jamIstirahat = [];
    protected array $trackerKelas = [];
    protected array $trackerRuang = [];
    protected array $limitasiWaktuDosen = [];

    // --- FITUR BARU: ARRAY UNTUK KARANTINA LINTAS KAMPUS ---
    protected array $karantinaHariDosen = [];

    public function __construct(JadwalGeneratorBatch $batch)
    {
        $this->batch = $batch;

        $config = $this->batch->config_snapshot;
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

        // --- FILTER RUANG BERDASARKAN KAMPUS ---
        $ruangQuery = RefRuang::where('is_active', 1)->orderBy('kapasitas', 'asc');
        // Jika form batch memiliki target kampus_id, filter ruangannya!
        if ($this->batch->kampus_id) {
            $ruangQuery->where('kampus_id', $this->batch->kampus_id);
        }
        $this->ruangTersedia = $ruangQuery->get()->toArray();

        $this->loadHardConstraints();

        $availabilities = \App\Models\DosenKetersediaan::all()->groupBy('dosen_id');
        foreach ($availabilities as $dosenId => $avails) {
            foreach ($avails as $avail) {
                $this->limitasiWaktuDosen[$dosenId][$avail->hari][] = [
                    'mulai' => substr($avail->jam_mulai, 0, 5),
                    'selesai' => substr($avail->jam_selesai, 0, 5)
                ];
            }
        }
    }

    protected function loadHardConstraints(): void
    {
        // PERBAIKAN: Kita ambil juga relasi 'ruang' untuk mengecek fisik kampus jadwal sebelumnya
        $existingJadwal = JadwalKuliah::with(['dosenPengampus', 'ruang'])
            ->where('tahun_akademik_id', $this->batch->tahun_akademik_id)
            ->get();

        $targetKampusId = $this->batch->kampus_id;

        foreach ($existingJadwal as $jadwal) {
            $hari = $jadwal->hari;
            $mulai = substr($jadwal->jam_mulai, 0, 5);
            $selesai = substr($jadwal->jam_selesai, 0, 5);
            $rentangWaktu = ['mulai' => $mulai, 'selesai' => $selesai];

            $jadwalKampusId = $jadwal->ruang ? $jadwal->ruang->kampus_id : null;

            if ($jadwal->ruang_id) {
                $this->trackerRuang[$jadwal->ruang_id][$hari][] = $rentangWaktu;
            }
            $this->trackerKelas[$jadwal->kelas_id][$hari][] = $rentangWaktu;

            foreach ($jadwal->dosenPengampus as $dosen) {
                // Tracking bentrok jam biasa
                $this->trackerDosen[$dosen->dosen_id][$hari][] = $rentangWaktu;

                // --- LOGIKA KARANTINA LINTAS KAMPUS ---
                // Jika jadwal ini berada di kampus yang BERBEDA dengan yang sedang di-generate
                if ($targetKampusId && $jadwalKampusId && $jadwalKampusId != $targetKampusId) {
                    $this->karantinaHariDosen[$dosen->dosen_id][] = $hari;
                }
            }
        }
    }

    public function execute(): void
    {
        if (empty($this->ruangTersedia)) {
            $this->batch->update([
                'status' => 'PREVIEW',
                'total_generated' => 0,
                'total_failed' => DosenPengampu::where('tahun_akademik_id', $this->batch->tahun_akademik_id)->count(),
                // Tambahkan alasan error di form agar BAAK tahu kenapa gagal total
                'failure_reason' => 'CRITICAL: Tidak ada ruangan aktif yang ditemukan untuk kampus ini.',
            ]);
            return;
        }
        // 1. Kumpulkan semua Matkul & Kelas yang SUDAH masuk ke SIAKAD (Production)
        $jadwalProduction = JadwalKuliah::where('tahun_akademik_id', $this->batch->tahun_akademik_id)
            ->get(['mata_kuliah_id', 'kelas_id']);

        $kombinasiSudahAda = [];
        foreach ($jadwalProduction as $jp) {
            $kombinasiSudahAda[] = $jp->mata_kuliah_id . '-' . $jp->kelas_id;
        }
        // 2. Tarik data beban mengajar
        $pengampus = DosenPengampu::with(['kelas', 'mataKuliah'])
            ->where('tahun_akademik_id', $this->batch->tahun_akademik_id)
            ->whereHas('kelas', function ($query) {
                $query->where('prodi_id', $this->batch->prodi_id);
            })
            ->get()
            // --- 3. FILTER SAKTI: Coret kelas yang sudah dibuat manual! ---
            ->filter(function ($item) use ($kombinasiSudahAda) {
                $key = $item->mata_kuliah_id . '-' . $item->kelas_id;
                return !in_array($key, $kombinasiSudahAda);
            })
            // --------------------------------------------------------------
            ->groupBy(function ($item) {
                return $item->mata_kuliah_id . '-' . $item->kelas_id;
            });

        // Jika setelah di-filter ternyata semua kelas sudah punya jadwal (kosong)
        if ($pengampus->isEmpty()) {
            $this->batch->update([
                'status' => 'PREVIEW',
                'total_generated' => 0,
                'total_failed' => 0,
                'failure_reason' => 'INFO: Semua beban mengajar prodi ini sudah memiliki jadwal di sistem utama (SIAKAD).',
            ]);
            return;
        }

        $totalGenerated = 0;
        $totalFailed = 0;

        foreach ($pengampus as $groupKey => $dosenList) {
            $firstItem = $dosenList->first();
            $kelasId = $firstItem->kelas_id;
            $mkId = $firstItem->mata_kuliah_id;
            $dosenIds = $dosenList->pluck('dosen_id')->toArray();

            $kelasProdiId = $firstItem->kelas->prodi_id ?? 0;
            $reqRuangId = $firstItem->ruang_id ?? null;

            $kapasitasDibutuhkan = MahasiswaKelas::where('kelas_id', $kelasId)->whereNull('tanggal_keluar')->count();
            $kapasitasDibutuhkan = $kapasitasDibutuhkan > 0 ? $kapasitasDibutuhkan : ($firstItem->kelas->kapasitas ?? 40);

            $kurikulumMK = KurikulumMataKuliah::where('mata_kuliah_id', $mkId)->first();
            $jenisRuangDibutuhkan = ($kurikulumMK && $kurikulumMK->sks_praktek > 0) ? 'LABORATORIUM' : 'TEORI';
            $sksTotal = $kurikulumMK ? ($kurikulumMK->sks_tatap_muka + $kurikulumMK->sks_praktek) : 2;

            $result = new JadwalGeneratorResult([
                'batch_id' => $this->batch->id,
                'mata_kuliah_id' => $mkId,
                'kelas_id' => $kelasId,
                'dosen_pengampu_ids' => $dosenList->pluck('id')->toArray(),
                'sks_real' => $sksTotal,
                'estimasi_kapasitas_dibutuhkan' => $kapasitasDibutuhkan,
            ]);

            $ruangSesuaiJenis = collect($this->ruangTersedia)->where('jenis_ruang', $jenisRuangDibutuhkan);

            if ($ruangSesuaiJenis->isEmpty()) {
                $result->is_success = false;
                $result->failure_reason = "CRITICAL: Kampus ini tidak memiliki ruangan aktif berjenis {$jenisRuangDibutuhkan}.";
                $totalFailed++;
                $result->save();
                continue;
            }

            $maxKapasitasTersedia = $ruangSesuaiJenis->max('kapasitas');
            if ($kapasitasDibutuhkan > $maxKapasitasTersedia && !$reqRuangId) {
                $result->is_success = false;
                $result->failure_reason = "CRITICAL: Butuh {$kapasitasDibutuhkan} kursi, tapi ruang {$jenisRuangDibutuhkan} di kampus ini maksimal hanya {$maxKapasitasTersedia} kursi.";
                $totalFailed++;
                $result->save();
                continue;
            }

            $alokasi = $this->findAvailableSlot(
                $dosenIds,
                $kelasId,
                $kelasProdiId,
                $jenisRuangDibutuhkan,
                $kapasitasDibutuhkan,
                $sksTotal,
                $reqRuangId
            );

            if ($alokasi['success']) {
                $result->is_success = true;
                $result->hari = $alokasi['hari'];
                $result->jam_mulai = $alokasi['jam_mulai'];
                $result->jam_selesai = $alokasi['jam_selesai'];
                $result->ruang_id = $alokasi['ruang_id'];

                $rentangWaktu = ['mulai' => $alokasi['jam_mulai'], 'selesai' => $alokasi['jam_selesai']];
                $this->trackerRuang[$alokasi['ruang_id']][$alokasi['hari']][] = $rentangWaktu;
                $this->trackerKelas[$kelasId][$alokasi['hari']][] = $rentangWaktu;
                foreach ($dosenIds as $dId) {
                    $this->trackerDosen[$dId][$alokasi['hari']][] = $rentangWaktu;
                }

                $totalGenerated++;
            } else {
                $result->is_success = false;
                $result->failure_reason = $alokasi['reason'];
                $totalFailed++;
            }

            $result->save();
        }

        $this->batch->update([
            'status' => 'PREVIEW',
            'total_generated' => $totalGenerated,
            'total_failed' => $totalFailed,
        ]);
    }

    protected function isTimeOverlap(array $trackerArr, int|string $id, string $hari, string $startTarget, string $endTarget): bool
    {
        if (!isset($trackerArr[$id][$hari])) {
            return false;
        }
        foreach ($trackerArr[$id][$hari] as $booked) {
            if ($startTarget < $booked['selesai'] && $endTarget > $booked['mulai']) {
                return true;
            }
        }
        return false;
    }

    protected function findAvailableSlot(array $dosenIds, int $kelasId, int $kelasProdiId, string $jenisRuang, int $kapasitas, int $sks, ?int $reqRuangId = null): array
    {
        $jamTutupKampus = $this->slotWaktu[count($this->slotWaktu) - 1]['selesai'];

        foreach ($this->hariOperasional as $hari) {

            // --- CEK KARANTINA LINTAS KAMPUS (MENGHEMAT KINERJA MESIN SANGAT SIGNIFIKAN) ---
            $terkenaKarantina = false;
            foreach ($dosenIds as $dId) {
                // Jika dosen ini terdaftar di array karantina pada hari ini (karena ngajar di kampus lain)
                if (isset($this->karantinaHariDosen[$dId]) && in_array($hari, $this->karantinaHariDosen[$dId])) {
                    $terkenaKarantina = true;
                    break;
                }
            }

            // Jika hari ini dikarantina untuk dosen tersebut, langsung lewati SATU HARI PENUH! 
            // Langsung cari ke hari besoknya (Misal dari Senin langsung lompat ke Selasa)
            if ($terkenaKarantina) {
                continue;
            }
            // ----------------------------------------------------------------------------------

            foreach ($this->slotWaktu as $slot) {
                $jamMulai = $slot['mulai'];

                if ($this->modeWaktu === 'statis') {
                    $jamSelesai = $slot['selesai'];
                    $durasiMenit = Carbon::parse($jamMulai)->diffInMinutes(Carbon::parse($jamSelesai));
                } else {
                    $durasiMenit = $sks * $this->menitPerSks;
                    $jamSelesai = Carbon::parse($jamMulai)->addMinutes($durasiMenit)->format('H:i');

                    if ($jamSelesai > $jamTutupKampus) {
                        continue;
                    }
                }

                $nabrakIstirahat = false;
                foreach ($this->jamIstirahat as $istirahat) {
                    if ($jamMulai < $istirahat['selesai'] && $jamSelesai > $istirahat['mulai']) {
                        $nabrakIstirahat = true;
                        break;
                    }
                }

                if ($nabrakIstirahat) continue;

                if ($this->isTimeOverlap($this->trackerKelas, $kelasId, $hari, $jamMulai, $jamSelesai)) {
                    continue;
                }

                $dosenBentrok = false;
                foreach ($dosenIds as $dId) {
                    if ($this->isTimeOverlap($this->trackerDosen, $dId, $hari, $jamMulai, $jamSelesai)) {
                        $dosenBentrok = true;
                        break;
                    }

                    if (isset($this->limitasiWaktuDosen[$dId])) {
                        $isAvailable = false;
                        if (isset($this->limitasiWaktuDosen[$dId][$hari])) {
                            foreach ($this->limitasiWaktuDosen[$dId][$hari] as $whitelist) {
                                if ($jamMulai >= $whitelist['mulai'] && $jamSelesai <= $whitelist['selesai']) {
                                    $isAvailable = true;
                                    break;
                                }
                            }
                        }

                        if (!$isAvailable) {
                            $dosenBentrok = true;
                            break;
                        }
                    }
                }

                if ($dosenBentrok) continue;

                foreach ($this->ruangTersedia as $ruang) {
                    if ($reqRuangId && $ruang['id'] != $reqRuangId) continue;
                    if (!is_null($ruang['prodi_id']) && $ruang['prodi_id'] != $kelasProdiId) continue;

                    $isRoomMatch = ($ruang['jenis_ruang'] === $jenisRuang && $ruang['kapasitas'] >= $kapasitas) || $reqRuangId;

                    if ($isRoomMatch) {
                        if (!$this->isTimeOverlap($this->trackerRuang, $ruang['id'], $hari, $jamMulai, $jamSelesai)) {
                            return [
                                'success' => true,
                                'hari' => $hari,
                                'jam_mulai' => $jamMulai,
                                'jam_selesai' => $jamSelesai,
                                'ruang_id' => $ruang['id'],
                            ];
                        }
                    }
                }
            }
        }

        return [
            'success' => false,
            'reason' => "Gagal: Ruang penuh / Dosen jadwalnya bentrok atau sedang mengajar di Kampus Cabang lain pada hari tersebut."
        ];
    }
}
