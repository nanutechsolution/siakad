<?php

namespace App\Services\Scheduling;

use App\Models\DosenPengampu;
use App\Models\JadwalGeneratorBatch;
use App\Models\JadwalGeneratorResult;
use App\Models\RefRuang;
use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\MahasiswaKelas;
use App\Models\KurikulumMataKuliah;
use App\Models\MasterKurikulum;
use Carbon\Carbon;

class JadwalGeneratorEngine
{
    protected JadwalGeneratorBatch $batch;

    protected array $ruangTersedia = [];

    protected array $hariOperasional = [];

    /**
     * Slot waktu dasar.
     *
     * Contoh:
     * 08:00 - 09:30
     * 09:30 - 11:00
     * dst.
     */
    protected array $slotWaktu = [];

    /**
     * Batas jam operasional setiap hari.
     *
     * Contoh:
     * [
     *     'Senin' => ['mulai' => '08:00', 'selesai' => '16:00'],
     *     'Jumat' => ['mulai' => '08:00', 'selesai' => '14:00'],
     * ]
     */
    protected array $jamOperasional = [];

    protected string $modeWaktu = 'dinamis';

    protected int $menitPerSks = 45;

    protected array $trackerDosen = [];

    protected array $jamIstirahat = [];

    protected array $trackerKelas = [];

    protected array $trackerRuang = [];

    protected array $limitasiWaktuDosen = [];

    /**
     * Dosen yang pada hari tertentu sudah mengajar
     * di kampus lain.
     */
    protected array $karantinaHariDosen = [];

    public function __construct(JadwalGeneratorBatch $batch)
    {
        $this->batch = $batch;

        /*
        |--------------------------------------------------------------------------
        | LOAD CONFIG
        |--------------------------------------------------------------------------
        */

        $config = $this->batch->config_snapshot;

        if (is_string($config)) {
            $config = json_decode($config, true);
        }

        $config = is_array($config) ? $config : [];

        /*
        |--------------------------------------------------------------------------
        | MODE WAKTU
        |--------------------------------------------------------------------------
        */

        $this->modeWaktu = $config['mode_waktu'] ?? 'dinamis';

        $this->menitPerSks = (int) (
            $config['menit_per_sks'] ?? 45
        );

        /*
        |--------------------------------------------------------------------------
        | HARI OPERASIONAL
        |--------------------------------------------------------------------------
        */

        $this->hariOperasional = !empty($config['hari'])
            ? $config['hari']
            : [
                'Senin',
                'Selasa',
                'Rabu',
                'Kamis',
                'Jumat',
            ];

        /*
        |--------------------------------------------------------------------------
        | SLOT WAKTU
        |--------------------------------------------------------------------------
        |
        | Ini adalah slot dasar.
        |
        | Batas sebenarnya tetap akan dicek berdasarkan
        | jam operasional masing-masing hari.
        |
        */

        $this->slotWaktu = !empty($config['slots'])
            ? $config['slots']
            : [
                [
                    'mulai' => '08:00',
                    'selesai' => '09:30',
                ],
                [
                    'mulai' => '09:30',
                    'selesai' => '11:00',
                ],
                [
                    'mulai' => '11:00',
                    'selesai' => '12:30',
                ],
                [
                    'mulai' => '13:00',
                    'selesai' => '14:30',
                ],
                [
                    'mulai' => '14:30',
                    'selesai' => '16:00',
                ],
            ];

        /*
        |--------------------------------------------------------------------------
        | JAM OPERASIONAL PER HARI
        |--------------------------------------------------------------------------
        |
        | INI BAGIAN PENTING UNTUK JUMAT.
        |
        | Jika config baru sudah punya jam_operasional,
        | gunakan konfigurasi tersebut.
        |
        | Kalau belum ada, gunakan default.
        |
        */

        $this->jamOperasional = [];

        if (!empty($config['jam_operasional'])) {
            foreach ($config['jam_operasional'] as $operasional) {
                if (
                    empty($operasional['hari']) ||
                    empty($operasional['mulai']) ||
                    empty($operasional['selesai'])
                ) {
                    continue;
                }

                $this->jamOperasional[$operasional['hari']] = [
                    'mulai' => substr($operasional['mulai'], 0, 5),
                    'selesai' => substr($operasional['selesai'], 0, 5),
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACK
        |--------------------------------------------------------------------------
        |
        | Supaya batch lama yang belum punya jam_operasional
        | tetap bisa dijalankan.
        |
        */

        if (empty($this->jamOperasional)) {
            $this->jamOperasional = [
                'Senin' => [
                    'mulai' => '08:00',
                    'selesai' => '16:00',
                ],

                'Selasa' => [
                    'mulai' => '08:00',
                    'selesai' => '16:00',
                ],

                'Rabu' => [
                    'mulai' => '08:00',
                    'selesai' => '16:00',
                ],

                'Kamis' => [
                    'mulai' => '08:00',
                    'selesai' => '16:00',
                ],

                'Jumat' => [
                    'mulai' => '08:00',
                    'selesai' => '14:00',
                ],

                'Sabtu' => [
                    'mulai' => '08:00',
                    'selesai' => '12:00',
                ],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | JAM ISTIRAHAT
        |--------------------------------------------------------------------------
        */

        $this->jamIstirahat = $config['jam_istirahat']
            ?? [
                [
                    'mulai' => '12:30',
                    'selesai' => '13:00',
                ],
            ];

        /*
        |--------------------------------------------------------------------------
        | FILTER RUANG BERDASARKAN KAMPUS
        |--------------------------------------------------------------------------
        */

        $ruangQuery = RefRuang::query()
            ->where('is_active', 1)
            ->orderBy('kapasitas', 'asc');

        if ($this->batch->kampus_id) {
            $ruangQuery->where(
                'kampus_id',
                $this->batch->kampus_id
            );
        }

        $this->ruangTersedia = $ruangQuery
            ->get()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | LOAD HARD CONSTRAINTS
        |--------------------------------------------------------------------------
        */

        $this->loadHardConstraints();

        /*
        |--------------------------------------------------------------------------
        | KETERSEDIAAN DOSEN
        |--------------------------------------------------------------------------
        */

        $availabilities = \App\Models\DosenKetersediaan::all()
            ->groupBy('dosen_id');

        foreach ($availabilities as $dosenId => $avails) {
            foreach ($avails as $avail) {

                $this->limitasiWaktuDosen[$dosenId][$avail->hari][] = [
                    'mulai' => substr(
                        $avail->jam_mulai,
                        0,
                        5
                    ),

                    'selesai' => substr(
                        $avail->jam_selesai,
                        0,
                        5
                    ),
                ];
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HARD CONSTRAINTS
    |--------------------------------------------------------------------------
    */

    protected function loadHardConstraints(): void
    {
        $existingJadwal = JadwalKuliah::with([
            'dosenPengampus',
            'ruang',
        ])
            ->where(
                'tahun_akademik_id',
                $this->batch->tahun_akademik_id
            )
            ->get();

        $targetKampusId = $this->batch->kampus_id;

        foreach ($existingJadwal as $jadwal) {

            $hari = $jadwal->hari;

            $mulai = substr(
                $jadwal->jam_mulai,
                0,
                5
            );

            $selesai = substr(
                $jadwal->jam_selesai,
                0,
                5
            );

            $rentangWaktu = [
                'mulai' => $mulai,
                'selesai' => $selesai,
            ];

            $jadwalKampusId = $jadwal->ruang
                ? $jadwal->ruang->kampus_id
                : null;

            /*
            |--------------------------------------------------------------------------
            | TRACK RUANG
            |--------------------------------------------------------------------------
            */

            if ($jadwal->ruang_id) {
                $this->trackerRuang[$jadwal->ruang_id][$hari][] = $rentangWaktu;
            }

            /*
            |--------------------------------------------------------------------------
            | TRACK KELAS
            |--------------------------------------------------------------------------
            */

            $this->trackerKelas[$jadwal->kelas_id][$hari][] = $rentangWaktu;

            /*
            |--------------------------------------------------------------------------
            | TRACK DOSEN
            |--------------------------------------------------------------------------
            */

            foreach ($jadwal->dosenPengampus as $dosen) {

                $this->trackerDosen[$dosen->dosen_id][$hari][] = $rentangWaktu;

                /*
                |--------------------------------------------------------------------------
                | KARANTINA LINTAS KAMPUS
                |--------------------------------------------------------------------------
                */

                if (
                    $targetKampusId &&
                    $jadwalKampusId &&
                    $jadwalKampusId != $targetKampusId
                ) {
                    $this->karantinaHariDosen[$dosen->dosen_id][] = $hari;
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EXECUTE
    |--------------------------------------------------------------------------
    */

    public function execute(): void
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI RUANG
        |--------------------------------------------------------------------------
        */

        if (empty($this->ruangTersedia)) {

            $this->batch->update([
                'status' => 'PREVIEW',
                'total_generated' => 0,

                'total_failed' =>
                DosenPengampu::where(
                    'tahun_akademik_id',
                    $this->batch->tahun_akademik_id
                )->count(),

                'failure_reason' =>
                'CRITICAL: Tidak ada ruangan aktif yang ditemukan untuk kampus ini.',
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | JADWAL YANG SUDAH ADA
        |--------------------------------------------------------------------------
        */

        $jadwalProduction = JadwalKuliah::where(
            'tahun_akademik_id',
            $this->batch->tahun_akademik_id
        )
            ->get([
                'mata_kuliah_id',
                'kelas_id',
            ]);

        $kombinasiSudahAda = [];

        foreach ($jadwalProduction as $jp) {

            $kombinasiSudahAda[] =
                $jp->mata_kuliah_id
                . '-'
                . $jp->kelas_id;
        }

        /*
        |--------------------------------------------------------------------------
        | TARIK DOSEN PENGAMPU
        |--------------------------------------------------------------------------
        */

        $pengampus = DosenPengampu::with([
            'kelas',
            'mataKuliah',
        ])
            ->where(
                'tahun_akademik_id',
                $this->batch->tahun_akademik_id
            )
            ->whereHas('kelas', function ($query) {

                $query
                    ->where(
                        'prodi_id',
                        $this->batch->prodi_id
                    )
                    ->where(
                        'kampus_id',
                        $this->batch->kampus_id
                    );
            })
            ->get()

            ->filter(function ($item) use (
                $kombinasiSudahAda
            ) {

                if (!$item->kelas) {
                    return false;
                }

                if (
                    (int) $item->kelas->kampus_id
                    !==
                    (int) $this->batch->kampus_id
                ) {
                    return false;
                }

                $key =
                    $item->mata_kuliah_id
                    . '-'
                    . $item->kelas_id;

                return !in_array(
                    $key,
                    $kombinasiSudahAda
                );
            })

            ->groupBy(function ($item) {

                return
                    $item->mata_kuliah_id
                    . '-'
                    . $item->kelas_id;
            });

        /*
        |--------------------------------------------------------------------------
        | TIDAK ADA BEBAN
        |--------------------------------------------------------------------------
        */

        if ($pengampus->isEmpty()) {

            $this->batch->update([
                'status' => 'PREVIEW',
                'total_generated' => 0,
                'total_failed' => 0,

                'failure_reason' =>
                'INFO: Semua beban mengajar prodi ini sudah memiliki jadwal di sistem utama (SIAKAD).',
            ]);

            return;
        }

        $totalGenerated = 0;
        $totalFailed = 0;

        /*
        |--------------------------------------------------------------------------
        | PROSES SATU PER SATU
        |--------------------------------------------------------------------------
        */

        foreach ($pengampus as $groupKey => $dosenList) {

            $firstItem = $dosenList->first();

            $kelasId = $firstItem->kelas_id;

            $mkId = $firstItem->mata_kuliah_id;

            $dosenIds = $dosenList
                ->pluck('dosen_id')
                ->toArray();

            $kelasProdiId =
                $firstItem->kelas->prodi_id ?? 0;

            $reqRuangId =
                $firstItem->ruang_id ?? null;

            /*
            |--------------------------------------------------------------------------
            | KAPASITAS
            |--------------------------------------------------------------------------
            */

            $kapasitasDibutuhkan =
                MahasiswaKelas::where(
                    'kelas_id',
                    $kelasId
                )
                ->whereNull('tanggal_keluar')
                ->count();

            $kapasitasDibutuhkan =
                $kapasitasDibutuhkan > 0
                ? $kapasitasDibutuhkan
                : (
                    $firstItem->kelas->kapasitas
                    ?? 40
                );

            /*
            |--------------------------------------------------------------------------
            | KURIKULUM
            |--------------------------------------------------------------------------
            */

            $kurikulumMK =
                $this->getKurikulumMataKuliahForKelas(
                    $mkId,
                    $firstItem->kelas
                );

            /*
            |--------------------------------------------------------------------------
            | JENIS RUANG
            |--------------------------------------------------------------------------
            */

            $jenisRuangDibutuhkan =
                (
                    $kurikulumMK &&
                    $kurikulumMK->sks_praktek > 0
                )
                ? 'LABORATORIUM'
                : 'TEORI';

            /*
            |--------------------------------------------------------------------------
            | TOTAL SKS
            |--------------------------------------------------------------------------
            */

            $sksTotal =
                $kurikulumMK
                ? (
                    (int) $kurikulumMK->sks_tatap_muka +
                    (int) $kurikulumMK->sks_praktek +
                    (int) $kurikulumMK->sks_lapangan
                )
                : 2;

            /*
            |--------------------------------------------------------------------------
            | RESULT
            |--------------------------------------------------------------------------
            */

            $result = new JadwalGeneratorResult([
                'batch_id' =>
                $this->batch->id,

                'mata_kuliah_id' =>
                $mkId,

                'kelas_id' =>
                $kelasId,

                'dosen_pengampu_ids' =>
                $dosenList
                    ->pluck('id')
                    ->toArray(),

                'sks_real' =>
                $sksTotal,

                'estimasi_kapasitas_dibutuhkan' =>
                $kapasitasDibutuhkan,
            ]);

            /*
            |--------------------------------------------------------------------------
            | FILTER RUANG
            |--------------------------------------------------------------------------
            */

            $ruangSesuaiJenis =
                collect($this->ruangTersedia)
                ->where(
                    'jenis_ruang',
                    $jenisRuangDibutuhkan
                );

            if ($ruangSesuaiJenis->isEmpty()) {

                $result->is_success = false;

                $result->failure_reason =
                    "CRITICAL: Kampus ini tidak memiliki ruangan aktif berjenis {$jenisRuangDibutuhkan}.";

                $totalFailed++;

                $result->save();

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDASI KAPASITAS
            |--------------------------------------------------------------------------
            */

            $maxKapasitasTersedia =
                $ruangSesuaiJenis->max('kapasitas');

            if (
                $kapasitasDibutuhkan >
                $maxKapasitasTersedia &&
                !$reqRuangId
            ) {

                $result->is_success = false;

                $result->failure_reason =
                    "CRITICAL: Butuh {$kapasitasDibutuhkan} kursi, "
                    . "tapi ruang {$jenisRuangDibutuhkan} "
                    . "di kampus ini maksimal hanya "
                    . "{$maxKapasitasTersedia} kursi.";

                $totalFailed++;

                $result->save();

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | CARI SLOT
            |--------------------------------------------------------------------------
            */

            $alokasi = $this->findAvailableSlot(
                $dosenIds,
                $kelasId,
                $kelasProdiId,
                $jenisRuangDibutuhkan,
                $kapasitasDibutuhkan,
                $sksTotal,
                $reqRuangId
            );

            /*
            |--------------------------------------------------------------------------
            | BERHASIL
            |--------------------------------------------------------------------------
            */

            if ($alokasi['success']) {

                $result->is_success = true;

                $result->hari =
                    $alokasi['hari'];

                $result->jam_mulai =
                    $alokasi['jam_mulai'];

                $result->jam_selesai =
                    $alokasi['jam_selesai'];

                $result->ruang_id =
                    $alokasi['ruang_id'];

                $rentangWaktu = [
                    'mulai' =>
                    $alokasi['jam_mulai'],

                    'selesai' =>
                    $alokasi['jam_selesai'],
                ];

                /*
                |--------------------------------------------------------------------------
                | UPDATE TRACKER RUANG
                |--------------------------------------------------------------------------
                */

                $this->trackerRuang[$alokasi['ruang_id']][$alokasi['hari']][] =
                    $rentangWaktu;

                /*
                |--------------------------------------------------------------------------
                | UPDATE TRACKER KELAS
                |--------------------------------------------------------------------------
                */

                $this->trackerKelas[$kelasId][$alokasi['hari']][] =
                    $rentangWaktu;

                /*
                |--------------------------------------------------------------------------
                | UPDATE TRACKER DOSEN
                |--------------------------------------------------------------------------
                */

                foreach ($dosenIds as $dId) {

                    $this->trackerDosen[$dId][$alokasi['hari']][] =
                        $rentangWaktu;
                }

                $totalGenerated++;
            }

            /*
            |--------------------------------------------------------------------------
            | GAGAL
            |--------------------------------------------------------------------------
            */ else {

                $result->is_success = false;

                $result->failure_reason =
                    $alokasi['reason'];

                $totalFailed++;
            }

            $result->save();
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE BATCH
        |--------------------------------------------------------------------------
        */

        $this->batch->update([
            'status' => 'PREVIEW',
            'total_generated' => $totalGenerated,
            'total_failed' => $totalFailed,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CEK OVERLAP
    |--------------------------------------------------------------------------
    */

    protected function isTimeOverlap(
        array $trackerArr,
        int|string $id,
        string $hari,
        string $startTarget,
        string $endTarget
    ): bool {

        if (
            !isset(
                $trackerArr[$id][$hari]
            )
        ) {
            return false;
        }

        foreach (
            $trackerArr[$id][$hari]
            as $booked
        ) {

            if (
                $startTarget < $booked['selesai']
                &&
                $endTarget > $booked['mulai']
            ) {
                return true;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | CARI JAM TUTUP PER HARI
    |--------------------------------------------------------------------------
    */

    protected function getJamOperasionalHari(
        string $hari
    ): ?array {

        if (
            !isset(
                $this->jamOperasional[$hari]
            )
        ) {
            return null;
        }

        return $this->jamOperasional[$hari];
    }

    /*
    |--------------------------------------------------------------------------
    | FIND AVAILABLE SLOT
    |--------------------------------------------------------------------------
    */

    protected function findAvailableSlot(
        array $dosenIds,
        int $kelasId,
        int $kelasProdiId,
        string $jenisRuang,
        int $kapasitas,
        int $sks,
        ?int $reqRuangId = null
    ): array {

        /*
        |--------------------------------------------------------------------------
        | LOOP HARI
        |--------------------------------------------------------------------------
        */

        foreach (
            $this->hariOperasional
            as $hari
        ) {

            /*
            |--------------------------------------------------------------------------
            | CEK JAM OPERASIONAL HARI
            |--------------------------------------------------------------------------
            */

            $operasional =
                $this->getJamOperasionalHari(
                    $hari
                );

            if (!$operasional) {
                continue;
            }

            $jamBukaHari =
                $operasional['mulai'];

            $jamTutupHari =
                $operasional['selesai'];

            /*
            |--------------------------------------------------------------------------
            | KARANTINA DOSEN LINTAS KAMPUS
            |--------------------------------------------------------------------------
            */

            $terkenaKarantina = false;

            foreach (
                $dosenIds as $dId
            ) {

                if (
                    isset(
                        $this->karantinaHariDosen[$dId]
                    )
                    &&
                    in_array(
                        $hari,
                        $this->karantinaHariDosen[$dId]
                    )
                ) {

                    $terkenaKarantina = true;

                    break;
                }
            }

            if ($terkenaKarantina) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | LOOP SLOT
            |--------------------------------------------------------------------------
            */

            foreach (
                $this->slotWaktu as $slot
            ) {

                $jamMulai =
                    substr(
                        $slot['mulai'],
                        0,
                        5
                    );

                /*
                |--------------------------------------------------------------------------
                | SLOT TIDAK BOLEH SEBELUM JAM BUKA
                |--------------------------------------------------------------------------
                */

                if (
                    $jamMulai < $jamBukaHari
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | HITUNG DURASI
                |--------------------------------------------------------------------------
                */

                if (
                    $this->modeWaktu === 'statis'
                ) {

                    $jamSelesai =
                        substr(
                            $slot['selesai'],
                            0,
                            5
                        );

                    $durasiMenit =
                        Carbon::parse(
                            $jamMulai
                        )->diffInMinutes(
                            Carbon::parse(
                                $jamSelesai
                            )
                        );
                } else {

                    $durasiMenit =
                        $sks *
                        $this->menitPerSks;

                    $jamSelesai =
                        Carbon::parse(
                            $jamMulai
                        )
                        ->addMinutes(
                            $durasiMenit
                        )
                        ->format('H:i');
                }

                /*
                |--------------------------------------------------------------------------
                | ⭐ BATAS JAM PER HARI
                |--------------------------------------------------------------------------
                |
                | Ini yang membuat Jumat berhenti di 14:00.
                |
                */

                if (
                    $jamSelesai >
                    $jamTutupHari
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | VALIDASI SLOT TIDAK BOLEH MELEWATI JAM OPERASIONAL
                |--------------------------------------------------------------------------
                */

                if (
                    $jamMulai <
                    $jamBukaHari
                    ||
                    $jamSelesai >
                    $jamTutupHari
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | CEK ISTIRAHAT
                |--------------------------------------------------------------------------
                */

                $nabrakIstirahat = false;

                foreach (
                    $this->jamIstirahat
                    as $istirahat
                ) {

                    $istirahatMulai =
                        substr(
                            $istirahat['mulai'],
                            0,
                            5
                        );

                    $istirahatSelesai =
                        substr(
                            $istirahat['selesai'],
                            0,
                            5
                        );

                    if (
                        $jamMulai <
                        $istirahatSelesai
                        &&
                        $jamSelesai >
                        $istirahatMulai
                    ) {

                        $nabrakIstirahat = true;

                        break;
                    }
                }

                if ($nabrakIstirahat) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | CEK KELAS
                |--------------------------------------------------------------------------
                */

                if (
                    $this->isTimeOverlap(
                        $this->trackerKelas,
                        $kelasId,
                        $hari,
                        $jamMulai,
                        $jamSelesai
                    )
                ) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | CEK DOSEN
                |--------------------------------------------------------------------------
                */

                $dosenBentrok = false;

                foreach (
                    $dosenIds as $dId
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | BENTROK JADWAL
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $this->isTimeOverlap(
                            $this->trackerDosen,
                            $dId,
                            $hari,
                            $jamMulai,
                            $jamSelesai
                        )
                    ) {

                        $dosenBentrok = true;

                        break;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | KETERSEDIAAN DOSEN
                    |--------------------------------------------------------------------------
                    */

                    if (
                        isset(
                            $this->limitasiWaktuDosen[$dId]
                        )
                    ) {

                        $isAvailable = false;

                        if (
                            isset(
                                $this->limitasiWaktuDosen[$dId][$hari]
                            )
                        ) {

                            foreach (
                                $this->limitasiWaktuDosen[$dId][$hari]
                                as $whitelist
                            ) {

                                if (
                                    $jamMulai >=
                                    $whitelist['mulai']
                                    &&
                                    $jamSelesai <=
                                    $whitelist['selesai']
                                ) {

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

                if ($dosenBentrok) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | LOOP RUANG
                |--------------------------------------------------------------------------
                */

                foreach (
                    $this->ruangTersedia
                    as $ruang
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | RUANG KHUSUS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $reqRuangId
                        &&
                        (int) $ruang['id']
                        !==
                        (int) $reqRuangId
                    ) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PEMBATASAN PRODI
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !is_null(
                            $ruang['prodi_id']
                        )
                        &&
                        (int) $ruang['prodi_id']
                        !==
                        (int) $kelasProdiId
                    ) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | JENIS RUANG
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $ruang['jenis_ruang']
                        !==
                        $jenisRuang
                    ) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | KAPASITAS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        (int) $ruang['kapasitas']
                        <
                        $kapasitas
                    ) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | BENTROK RUANG
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $this->isTimeOverlap(
                            $this->trackerRuang,
                            $ruang['id'],
                            $hari,
                            $jamMulai,
                            $jamSelesai
                        )
                    ) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SLOT VALID
                    |--------------------------------------------------------------------------
                    */

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

        /*
        |--------------------------------------------------------------------------
        | SEMUA SLOT GAGAL
        |--------------------------------------------------------------------------
        */

        return [
            'success' => false,

            'reason' =>
            'Gagal: Tidak ditemukan slot yang memenuhi seluruh constraint '
                . '(ruang, kapasitas, dosen, kelas, jam operasional, '
                . 'waktu istirahat, atau konflik lintas kampus).',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CARI KURIKULUM MATA KULIAH
    |--------------------------------------------------------------------------
    */

    protected function getKurikulumMataKuliahForKelas(
        int $mataKuliahId,
        Kelas $kelas
    ): ?KurikulumMataKuliah {

        $tahunAngkatan =
            (int) $kelas->angkatan_id;

        $kurikulum = MasterKurikulum::query()
            ->where(
                'prodi_id',
                $kelas->prodi_id
            )
            ->where(
                'tahun_mulai',
                '<=',
                $tahunAngkatan
            )
            ->orderByDesc(
                'tahun_mulai'
            )
            ->first();

        if (!$kurikulum) {
            return null;
        }

        return KurikulumMataKuliah::query()
            ->where(
                'kurikulum_id',
                $kurikulum->id
            )
            ->where(
                'mata_kuliah_id',
                $mataKuliahId
            )
            ->first();
    }
}
