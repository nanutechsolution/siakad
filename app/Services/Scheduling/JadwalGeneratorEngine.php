<?php

namespace App\Services\Scheduling;

use App\Models\DosenPengampu;
use App\Models\JadwalGeneratorBatch;
use App\Models\JadwalGeneratorResult;
use App\Models\RefRuang;
use App\Models\JadwalKuliah;
use App\Models\MahasiswaKelas;
use App\Models\KurikulumMataKuliah;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JadwalGeneratorEngine
{
    protected JadwalGeneratorBatch $batch;
    protected array $ruangTersedia;
    protected array $hariOperasional;
    protected array $slotWaktu;

    // Memory arrays untuk tracking bentrok secara cepat tanpa query database berulang
    protected array $trackerDosen = [];
    protected array $trackerKelas = [];
    protected array $trackerRuang = [];

    public function __construct(JadwalGeneratorBatch $batch)
    {
        $this->batch = $batch;

        // --- PERBAIKAN 1: BACA CONFIG DENGAN AMAN ---
        $config = $this->batch->config_snapshot;
        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        // Load konfigurasi dari batch (misal: Senin-Jumat)
        $this->hariOperasional = !empty($config['hari']) ? $config['hari'] : ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        // Blok waktu fix sesuai standar PDF kampus (1 Sesi = 90 Menit / 2 SKS)
        $this->slotWaktu = !empty($config['slots']) ? $config['slots'] : [
            ['mulai' => '08:00', 'selesai' => '09:30'],
            ['mulai' => '09:30', 'selesai' => '11:00'],
            ['mulai' => '11:00', 'selesai' => '12:30'],
            // 12:30 - 13:00 Istirahat Otomatis Terlewati
            ['mulai' => '13:00', 'selesai' => '14:30'],
            ['mulai' => '14:30', 'selesai' => '16:00'],
            ['mulai' => '16:00', 'selesai' => '17:00'],
        ];

        // --- PERBAIKAN 2: PASTIKAN MENGAMBIL RUANGAN AKTIF ---
        $this->ruangTersedia = RefRuang::where('is_active', 1)
            ->orderBy('kapasitas', 'asc') // Sort dari terkecil untuk efisiensi ruang
            ->get()
            ->toArray();

        $this->loadHardConstraints();
    }

    /**
     * Memuat jadwal permanen existing sebagai tembok/penghalang algoritma
     */
    protected function loadHardConstraints(): void
    {
        $existingJadwal = JadwalKuliah::with('dosenPengampus')
            ->where('tahun_akademik_id', $this->batch->tahun_akademik_id)
            ->get();

        foreach ($existingJadwal as $jadwal) {
            $hariJam = $jadwal->hari . '-' . $jadwal->jam_mulai;

            // Blokir Ruang
            if ($jadwal->ruang_id) {
                $this->trackerRuang[$jadwal->ruang_id][] = $hariJam;
            }

            // Blokir Kelas
            $this->trackerKelas[$jadwal->kelas_id][] = $hariJam;

            // Blokir Dosen
            foreach ($jadwal->dosenPengampus as $dosen) {
                $this->trackerDosen[$dosen->dosen_id][] = $hariJam;
            }
        }
    }

    /**
     * Eksekusi Algoritma Constraint Satisfaction (CSP)
     */
    public function execute(): void
    {
        // Jika ruangan kosong dari awal, langsung gagalkan semua (karena tidak mungkin bisa plot)
        if (empty($this->ruangTersedia)) {
            $this->batch->update([
                'status' => 'PREVIEW',
                'total_generated' => 0,
                'total_failed' => DosenPengampu::where('tahun_akademik_id', $this->batch->tahun_akademik_id)->count(),
            ]);
            return;
        }

        // 1. Ambil data target yang harus dijadwalkan (Group by MK dan Kelas)
        $pengampus = DosenPengampu::with(['kelas', 'mataKuliah'])
            ->where('tahun_akademik_id', $this->batch->tahun_akademik_id)
            ->get()
            ->groupBy(function ($item) {
                return $item->mata_kuliah_id . '-' . $item->kelas_id;
            });

        $totalGenerated = 0;
        $totalFailed = 0;

        foreach ($pengampus as $groupKey => $dosenList) {
            $firstItem = $dosenList->first();
            $kelasId = $firstItem->kelas_id;
            $mkId = $firstItem->mata_kuliah_id;
            $dosenIds = $dosenList->pluck('dosen_id')->toArray();

            // Tarik Prodi Kelas & Request Ruang Kaprodi
            $kelasProdiId = $firstItem->kelas->prodi_id ?? 0;
            $reqRuangId = $firstItem->ruang_id ?? null;

            // 2. Hitung Kebutuhan Kapasitas
            $kapasitasDibutuhkan = MahasiswaKelas::where('kelas_id', $kelasId)
                ->whereNull('tanggal_keluar')
                ->count();

            // Default kapasitas jika kelas belum ada mahasiswa KRS
            $kapasitasDibutuhkan = $kapasitasDibutuhkan > 0 ? $kapasitasDibutuhkan : ($firstItem->kelas->kapasitas ?? 40);

            // 3. Tentukan Jenis Ruang (Teori / Lab) berdasarkan Kurikulum
            $kurikulumMK = KurikulumMataKuliah::where('mata_kuliah_id', $mkId)->first();
            $jenisRuangDibutuhkan = ($kurikulumMK && $kurikulumMK->sks_praktek > 0) ? 'LABORATORIUM' : 'TEORI';
            $sksTotal = $kurikulumMK ? ($kurikulumMK->sks_tatap_muka + $kurikulumMK->sks_praktek) : 2;

            // 4. Cari Slot Kosong (Matchmaking)
            $alokasi = $this->findAvailableSlot(
                $dosenIds,
                $kelasId,
                $kelasProdiId,
                $jenisRuangDibutuhkan,
                $kapasitasDibutuhkan,
                $sksTotal,
                $reqRuangId
            );

            // 5. Simpan Hasil ke Sandbox (JadwalGeneratorResult)
            $result = new JadwalGeneratorResult([
                'batch_id' => $this->batch->id,
                'mata_kuliah_id' => $mkId,
                'kelas_id' => $kelasId,
                'dosen_pengampu_ids' => $dosenList->pluck('id')->toArray(),
                'sks_real' => $sksTotal,
                'estimasi_kapasitas_dibutuhkan' => $kapasitasDibutuhkan,
            ]);

            if ($alokasi['success']) {
                $result->is_success = true;
                $result->hari = $alokasi['hari'];
                $result->jam_mulai = $alokasi['jam_mulai'];
                $result->jam_selesai = $alokasi['jam_selesai'];
                $result->ruang_id = $alokasi['ruang_id'];

                // Kunci slot ini di memory tracker agar tidak dipakai MK selanjutnya
                $hariJam = $alokasi['hari'] . '-' . $alokasi['jam_mulai'];
                $this->trackerRuang[$alokasi['ruang_id']][] = $hariJam;
                $this->trackerKelas[$kelasId][] = $hariJam;
                foreach ($dosenIds as $dId) {
                    $this->trackerDosen[$dId][] = $hariJam;
                }

                $totalGenerated++;
            } else {
                $result->is_success = false;
                $result->failure_reason = $alokasi['reason'];
                $totalFailed++;
            }

            $result->save();
        }

        // Update Batch Status
        $this->batch->update([
            'status' => 'PREVIEW',
            'total_generated' => $totalGenerated,
            'total_failed' => $totalFailed,
        ]);
    }

    /**
     * CSP Matchmaking Rule: Mencari irisan Ruang, Waktu, Dosen, dan Kelas yang kosong.
     */
    protected function findAvailableSlot(array $dosenIds, int $kelasId, int $kelasProdiId, string $jenisRuang, int $kapasitas, int $sks, ?int $reqRuangId = null): array
    {
        foreach ($this->hariOperasional as $hari) {
            foreach ($this->slotWaktu as $slot) {
                $hariJam = $hari . '-' . $slot['mulai'];

                // Cek Bentrok Kelas
                if (isset($this->trackerKelas[$kelasId]) && in_array($hariJam, $this->trackerKelas[$kelasId])) {
                    continue;
                }

                // Cek Bentrok Dosen (Semua dosen di tim teaching harus kosong)
                $dosenBentrok = false;
                foreach ($dosenIds as $dId) {
                    if (isset($this->trackerDosen[$dId]) && in_array($hariJam, $this->trackerDosen[$dId])) {
                        $dosenBentrok = true;
                        break;
                    }
                }
                if ($dosenBentrok) continue;

                // Cek Ruangan Tersedia
                foreach ($this->ruangTersedia as $ruang) {

                    // 1. ATURAN RUANG SPESIFIK (REQUEST KAPRODI)
                    if ($reqRuangId && $ruang['id'] != $reqRuangId) {
                        continue;
                    }

                    // 2. ATURAN RUANG EKSKLUSIF PRODI
                    if (!is_null($ruang['prodi_id']) && $ruang['prodi_id'] != $kelasProdiId) {
                        continue;
                    }

                    // 3. CEK KAPASITAS & JENIS
                    $isRoomMatch = ($ruang['jenis_ruang'] === $jenisRuang && $ruang['kapasitas'] >= $kapasitas) || $reqRuangId;

                    if ($isRoomMatch) {
                        // Cek Ruang Kosong (Belum dipakai di jam yang sama)
                        if (!isset($this->trackerRuang[$ruang['id']]) || !in_array($hariJam, $this->trackerRuang[$ruang['id']])) {
                            return [
                                'success' => true,
                                'hari' => $hari,
                                'jam_mulai' => $slot['mulai'],
                                'jam_selesai' => $slot['selesai'],
                                'ruang_id' => $ruang['id'],
                            ];
                        }
                    }
                }
            }
        }

        $totalRuangAktif = count($this->ruangTersedia);
        return [
            'success' => false,
            'reason' => "Gagal: Butuh ruang jenis {$jenisRuang} (Min. Kapasitas: {$kapasitas}). Total ruangan aktif di DB: {$totalRuangAktif}. Pastikan tidak ada bentrok waktu dosen/kelas, ATAU kapasitas ruangan Anda cukup."
        ];
    }
}
