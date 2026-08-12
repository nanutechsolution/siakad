<?php

namespace App\Services;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikMode;
use App\Enums\PembimbingAkademikStatus;
use App\Exceptions\PembimbingAkademikException;
use App\Models\Kelas;
use App\Models\KonfigurasiPembimbingAkademik;
use App\Models\Mahasiswa;
use App\Models\PembimbingAkademik;
use App\Models\TrxDosen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PembimbingAkademikService
{
    /**
     * Cari konfigurasi mode yang AKTIF untuk kombinasi prodi + angkatan.
     *
     * Null berarti belum diatur / sedang dinonaktifkan.
     */
    public function konfigurasiAktif(
        ?int $prodiId,
        ?int $angkatanId
    ): ?KonfigurasiPembimbingAkademik {
        if (! $prodiId || ! $angkatanId) {
            return null;
        }

        return KonfigurasiPembimbingAkademik::query()
            ->where('prodi_id', $prodiId)
            ->where('angkatan_id', $angkatanId)
            ->where('aktif', true)
            ->first();
    }

    /**
     * Tentukan mode penetapan yang berlaku untuk suatu jenis pembimbing.
     *
     * Dosen Wali:
     * - mengikuti konfigurasi aktif;
     * - null jika konfigurasi belum tersedia.
     *
     * Jenis lain:
     * - selalu PER_MAHASISWA.
     */
    public function modeUntuk(
        PembimbingAkademikJenis $jenis,
        ?KonfigurasiPembimbingAkademik $konfigurasi
    ): ?PembimbingAkademikMode {
        if ($jenis !== PembimbingAkademikJenis::DOSEN_WALI) {
            return PembimbingAkademikMode::PER_MAHASISWA;
        }

        return $konfigurasi?->mode;
    }

    /**
     * Cek apakah target sudah memiliki pembimbing aktif.
     *
     * Catatan:
     * Method ini digunakan untuk pengecekan umum.
     * Untuk Dosen Wali, validasi khusus per-mode dilakukan di tugaskan().
     */
    public function sudahPunyaPembimbingAktif(
        PembimbingAkademikJenis $jenis,
        ?int $kelasId,
        ?string $mahasiswaId
    ): bool {
        return PembimbingAkademik::query()
            ->where('jenis', $jenis)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->when(
                $kelasId !== null,
                fn(Builder $q) => $q->where('kelas_id', $kelasId)
            )
            ->when(
                $mahasiswaId !== null,
                fn(Builder $q) => $q->where('mahasiswa_id', $mahasiswaId)
            )
            ->exists();
    }

    /**
     * Kelas yang belum memiliki Dosen Wali aktif
     * untuk prodi + angkatan tertentu.
     */
    public function kelasBelumPunyaWali(
        int $prodiId,
        int $angkatanId
    ): Collection {
        return Kelas::query()
            ->where('prodi_id', $prodiId)
            ->where('angkatan_id', $angkatanId)
            ->whereDoesntHave(
                'pembimbingAkademik',
                fn(Builder $q) => $q
                    ->where(
                        'jenis',
                        PembimbingAkademikJenis::DOSEN_WALI
                    )
                    ->where(
                        'status',
                        PembimbingAkademikStatus::AKTIF
                    )
            )
            ->orderBy('nama_kelas')
            ->pluck('nama_kelas', 'id');
    }

    /**
     * Buat penugasan pembimbing baru.
     *
     * Aturan utama:
     *
     * DOSEN_WALI:
     * - PER_KELAS       => wajib kelas_id, mahasiswa_id harus null
     * - PER_MAHASISWA   => wajib mahasiswa_id, kelas_id harus null
     * - konfigurasi harus aktif
     *
     * Jenis selain DOSEN_WALI:
     * - selalu PER_MAHASISWA
     * - wajib mahasiswa_id
     * - kelas_id harus null
     *
     * Prodi dan angkatan TIDAK dipercaya dari request.
     * Nilainya diambil dari database berdasarkan target.
     *
     * @param array{
     *     jenis: string|\App\Enums\PembimbingAkademikJenis,
     *     kelas_id?: int|null,
     *     mahasiswa_id?: string|null,
     *     dosen_id: string,
     *     is_primary?: bool,
     *     semester_mulai_id: int,
     *     tanggal_mulai: string,
     *     nomor_sk?: string|null,
     *     tanggal_sk?: string|null,
     *     keterangan?: string|null,
     *     prodi_id?: int|null,
     *     angkatan_id?: int|null
     * } $data
     */
    public function tugaskan(array $data): PembimbingAkademik
    {
        $jenis = $data['jenis'] instanceof PembimbingAkademikJenis
            ? $data['jenis']
            : PembimbingAkademikJenis::from($data['jenis']);

        $kelasId = isset($data['kelas_id'])
            ? (int) $data['kelas_id']
            : null;

        $mahasiswaId = filled($data['mahasiswa_id'] ?? null)
            ? (string) $data['mahasiswa_id']
            : null;

        /*
         * ================================================================
         * 1. VALIDASI TARGET DASAR
         * ================================================================
         */

        if ($kelasId === null && $mahasiswaId === null) {
            throw PembimbingAkademikException::targetKosong();
        }

        /*
         * ================================================================
         * 2. TENTUKAN MODE
         * ================================================================
         *
         * Untuk Dosen Wali, konfigurasi diambil dari DATABASE berdasarkan
         * target. Jangan percaya prodi_id / angkatan_id dari request.
         */

        $konfigurasi = null;
        $modeEfektif = null;
        $prodiId = null;
        $angkatanId = null;

        if ($jenis === PembimbingAkademikJenis::DOSEN_WALI) {
            /*
             * ------------------------------------------------------------
             * Target PER_KELAS
             * ------------------------------------------------------------
             */
            if ($kelasId !== null) {
                if ($mahasiswaId !== null) {
                    throw PembimbingAkademikException::targetTidakSesuaiMode();
                }

                $kelas = Kelas::query()->find($kelasId);

                if (! $kelas) {
                    throw PembimbingAkademikException::targetKosong();
                }

                $prodiId = $kelas->prodi_id;
                $angkatanId = $kelas->angkatan_id;
            }

            /*
             * ------------------------------------------------------------
             * Target PER_MAHASISWA
             * ------------------------------------------------------------
             */
            if ($mahasiswaId !== null) {
                if ($kelasId !== null) {
                    throw PembimbingAkademikException::targetTidakSesuaiMode();
                }

                $mahasiswa = Mahasiswa::query()
                    ->whereNull('deleted_at')
                    ->find($mahasiswaId);

                if (! $mahasiswa) {
                    throw PembimbingAkademikException::targetKosong();
                }

                $prodiId = $mahasiswa->prodi_id;
                $angkatanId = $mahasiswa->angkatan_id;
            }

            /*
             * ------------------------------------------------------------
             * Ambil konfigurasi dari DATABASE.
             * BUKAN dari prodi_id / angkatan_id request.
             * ------------------------------------------------------------
             */
            $konfigurasi = $this->konfigurasiAktif(
                $prodiId,
                $angkatanId
            );

            $modeEfektif = $this->modeUntuk(
                $jenis,
                $konfigurasi
            );

            if (! $modeEfektif) {
                throw PembimbingAkademikException::konfigurasiBelumDiatur();
            }

            /*
             * ------------------------------------------------------------
             * Pastikan target sesuai konfigurasi.
             * ------------------------------------------------------------
             */
            if (
                $modeEfektif === PembimbingAkademikMode::PER_KELAS
                && ($kelasId === null || $mahasiswaId !== null)
            ) {
                throw PembimbingAkademikException::targetTidakSesuaiMode();
            }

            if (
                $modeEfektif === PembimbingAkademikMode::PER_MAHASISWA
                && ($mahasiswaId === null || $kelasId !== null)
            ) {
                throw PembimbingAkademikException::targetTidakSesuaiMode();
            }
        }

        /*
         * ================================================================
         * 3. JENIS SELAIN DOSEN WALI
         * ================================================================
         *
         * Pembimbing Skripsi / Tesis / PKL / dst selalu individual.
         */

        if ($jenis !== PembimbingAkademikJenis::DOSEN_WALI) {
            if ($mahasiswaId === null || $kelasId !== null) {
                throw PembimbingAkademikException::targetTidakSesuaiMode();
            }
        }

        /*
         * ================================================================
         * 4. VALIDASI DUPLIKASI
         * ================================================================
         */

        if ($jenis === PembimbingAkademikJenis::DOSEN_WALI) {
            /*
             * PER_KELAS:
             * satu kelas hanya boleh punya satu wali aktif.
             */
            if ($modeEfektif === PembimbingAkademikMode::PER_KELAS) {
                if (
                    $this->sudahPunyaPembimbingAktif(
                        $jenis,
                        $kelasId,
                        null
                    )
                ) {
                    throw PembimbingAkademikException::sudahAdaPembimbingAktif();
                }
            }

            /*
             * PER_MAHASISWA:
             * satu mahasiswa hanya boleh punya satu wali individual aktif.
             */
            if ($modeEfektif === PembimbingAkademikMode::PER_MAHASISWA) {
                if (
                    $this->sudahPunyaPembimbingAktif(
                        $jenis,
                        null,
                        $mahasiswaId
                    )
                ) {
                    throw PembimbingAkademikException::sudahAdaPembimbingAktif();
                }

                /*
                 * Defense in depth:
                 *
                 * Jangan sampai mahasiswa yang masih tercakup wali kelas
                 * mendapatkan wali individual.
                 *
                 * Ini penting ketika konfigurasi pernah berubah dari
                 * PER_KELAS menjadi PER_MAHASISWA.
                 */
                if (
                    $mahasiswaId !== null
                    && $this->kelasMahasiswaSudahPunyaWali($mahasiswaId)
                ) {
                    throw PembimbingAkademikException::sudahDicoverWaliKelas();
                }
            }
        } else {
            /*
             * Jenis pembimbing selain Dosen Wali:
             * cek assignment individual mahasiswa.
             */
            if (
                $this->sudahPunyaPembimbingAktif(
                    $jenis,
                    null,
                    $mahasiswaId
                )
            ) {
                throw PembimbingAkademikException::sudahAdaPembimbingAktif();
            }
        }

        /*
         * ================================================================
         * 5. CREATE
         * ================================================================
         */

        return DB::transaction(
            fn() => PembimbingAkademik::create([
                'kelas_id' => $kelasId,
                'mahasiswa_id' => $mahasiswaId,
                'dosen_id' => $data['dosen_id'],
                'jenis' => $jenis,
                'is_primary' => $data['is_primary'] ?? true,
                'semester_mulai_id' => $data['semester_mulai_id'],
                'tanggal_mulai' => $data['tanggal_mulai'],
                'nomor_sk' => $data['nomor_sk'] ?? null,
                'tanggal_sk' => $data['tanggal_sk'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'status' => PembimbingAkademikStatus::AKTIF,
                'created_by' => auth()->id(),
            ])
        );
    }

    /**
     * Mutasi (ganti dosen) untuk assignment aktif.
     *
     * Record lama ditutup dan record baru dibuat agar histori utuh.
     *
     * @param array{
     *     dosen_id: string,
     *     tanggal_mulai: string,
     *     semester_mulai_id: int,
     *     nomor_sk?: string|null,
     *     tanggal_sk?: string|null,
     *     alasan: string
     * } $data
     */
    public function mutasi(
        PembimbingAkademik $record,
        array $data
    ): PembimbingAkademik {
        if (
            (string) $data['dosen_id']
            ===
            (string) $record->dosen_id
        ) {
            throw PembimbingAkademikException::dosenPenggantiSama();
        }

        if (
            $record->tanggal_mulai
            &&
            (string) $data['tanggal_mulai']
            <
            $record->tanggal_mulai->toDateString()
        ) {
            throw PembimbingAkademikException::tanggalMulaiTidakValid();
        }

        return DB::transaction(function () use ($record, $data) {
            $record->update([
                'status' => PembimbingAkademikStatus::SELESAI,
                'tanggal_selesai' => $data['tanggal_mulai'],
                'semester_selesai_id' => $data['semester_mulai_id'],
                'updated_by' => auth()->id(),
            ]);

            return PembimbingAkademik::create([
                'kelas_id' => $record->kelas_id,
                'mahasiswa_id' => $record->mahasiswa_id,
                'dosen_id' => $data['dosen_id'],
                'jenis' => $record->jenis,
                'is_primary' => $record->is_primary,
                'semester_mulai_id' => $data['semester_mulai_id'],
                'tanggal_mulai' => $data['tanggal_mulai'],
                'nomor_sk' => $data['nomor_sk'] ?? null,
                'tanggal_sk' => $data['tanggal_sk'] ?? null,
                'alasan' => $data['alasan'],
                'status' => PembimbingAkademikStatus::AKTIF,
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Batalkan penugasan aktif tanpa menggantinya.
     */
    public function batalkan(
        PembimbingAkademik $record,
        string $alasan
    ): PembimbingAkademik {
        $record->update([
            'status' => PembimbingAkademikStatus::DIBATALKAN,
            'tanggal_selesai' => now()->toDateString(),
            'alasan' => $alasan,
            'updated_by' => auth()->id(),
        ]);

        return $record->refresh();
    }

    /**
     * Cek apakah kelas aktif mahasiswa memiliki Dosen Wali aktif.
     *
     * Relasi:
     *
     * mahasiswa
     *     -> mahasiswa_kelas
     *         -> kelas
     *             -> pembimbing_akademik
     */
    protected function kelasMahasiswaSudahPunyaWali(
        string $mahasiswaId
    ): bool {
        return DB::table('mahasiswa_kelas')
            ->join(
                'pembimbing_akademik',
                'pembimbing_akademik.kelas_id',
                '=',
                'mahasiswa_kelas.kelas_id'
            )
            ->where(
                'mahasiswa_kelas.mahasiswa_id',
                $mahasiswaId
            )
            ->whereNull('mahasiswa_kelas.tanggal_keluar')
            ->where(
                'pembimbing_akademik.jenis',
                PembimbingAkademikJenis::DOSEN_WALI->value
            )
            ->where(
                'pembimbing_akademik.status',
                PembimbingAkademikStatus::AKTIF->value
            )
            ->exists();
    }

    /**
     * Total assignment Dosen Wali aktif yang bergantung
     * pada kombinasi prodi + angkatan.
     */
    public function totalPenugasanAktifUntukKombinasi(
        ?int $prodiId,
        ?int $angkatanId
    ): int {
        if (! $prodiId || ! $angkatanId) {
            return 0;
        }

        return PembimbingAkademik::query()
            ->where(
                'jenis',
                PembimbingAkademikJenis::DOSEN_WALI
            )
            ->where(
                'status',
                PembimbingAkademikStatus::AKTIF
            )
            ->where(function (Builder $q) use (
                $prodiId,
                $angkatanId
            ) {
                $q
                    ->whereHas(
                        'kelas',
                        fn(Builder $kelas) => $kelas
                            ->where('prodi_id', $prodiId)
                            ->where('angkatan_id', $angkatanId)
                    )
                    ->orWhereHas(
                        'mahasiswa',
                        fn(Builder $mahasiswa) => $mahasiswa
                            ->where('prodi_id', $prodiId)
                            ->where('angkatan_id', $angkatanId)
                    );
            })
            ->count();
    }

    /**
     * Total mahasiswa aktif.
     */
    public function totalMahasiswaAktif(): int
    {
        return Mahasiswa::query()
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Total mahasiswa yang sudah memiliki Dosen Wali.
     *
     * Termasuk:
     * - assignment individual;
     * - mahasiswa yang kelas aktifnya memiliki wali kelas.
     */
    public function totalSudahPunyaWali(): int
    {
        return $this->queryMahasiswaSudahPunyaWali()->count();
    }

    /**
     * Total mahasiswa yang belum memiliki Dosen Wali.
     */
    public function totalBelumPunyaWali(): int
    {
        return max(
            0,
            $this->totalMahasiswaAktif()
                - $this->totalSudahPunyaWali()
        );
    }

    /**
     * Query mahasiswa yang sudah punya wali.
     *
     * Definisi:
     *
     * 1. Punya assignment Dosen Wali individual aktif
     * ATAU
     * 2. Kelas aktifnya punya Dosen Wali aktif.
     */
    protected function queryMahasiswaSudahPunyaWali(): Builder
    {
        return Mahasiswa::query()
            ->whereNull('deleted_at')
            ->where(function (Builder $query) {
                $query
                    ->whereHas(
                        'pembimbingAkademik',
                        fn(Builder $q) => $q
                            ->where(
                                'jenis',
                                PembimbingAkademikJenis::DOSEN_WALI
                            )
                            ->where(
                                'status',
                                PembimbingAkademikStatus::AKTIF
                            )
                    )
                    ->orWhereHas(
                        'mahasiswaKelas',
                        function (Builder $q) {
                            $q
                                ->whereNull('tanggal_keluar')
                                ->whereHas(
                                    'kelas',
                                    function (Builder $kelas) {
                                        $kelas->whereHas(
                                            'pembimbingAkademik',
                                            fn(Builder $wali) => $wali
                                                ->where(
                                                    'jenis',
                                                    PembimbingAkademikJenis::DOSEN_WALI
                                                )
                                                ->where(
                                                    'status',
                                                    PembimbingAkademikStatus::AKTIF
                                                )
                                        );
                                    }
                                );
                        }
                    );
            });
    }

    /**
     * Query mahasiswa yang belum punya wali.
     *
     * Tidak memiliki:
     * - Dosen Wali individual aktif
     * DAN
     * - wali kelas aktif pada kelas aktifnya.
     */
    public function queryMahasiswaTanpaWali(): Builder
    {
        return Mahasiswa::query()
            ->whereNull('deleted_at')
            ->whereDoesntHave(
                'pembimbingAkademik',
                fn(Builder $q) => $q
                    ->where(
                        'jenis',
                        PembimbingAkademikJenis::DOSEN_WALI
                    )
                    ->where(
                        'status',
                        PembimbingAkademikStatus::AKTIF
                    )
            )
            ->whereDoesntHave(
                'mahasiswaKelas',
                function (Builder $q) {
                    $q
                        ->whereNull('tanggal_keluar')
                        ->whereHas(
                            'kelas',
                            function (Builder $kelas) {
                                $kelas->whereHas(
                                    'pembimbingAkademik',
                                    fn(Builder $wali) => $wali
                                        ->where(
                                            'jenis',
                                            PembimbingAkademikJenis::DOSEN_WALI
                                        )
                                        ->where(
                                            'status',
                                            PembimbingAkademikStatus::AKTIF
                                        )
                                );
                            }
                        );
                }
            );
    }

    /**
     * Top-N dosen dengan beban Dosen Wali aktif terbanyak.
     *
     * Beban:
     *
     * PER_MAHASISWA:
     *     1 assignment = 1 mahasiswa
     *
     * PER_KELAS:
     *     1 assignment = jumlah mahasiswa aktif di kelas
     *
     * @return Collection<int, array{
     *     dosen: TrxDosen,
     *     total: int
     * }>
     */
    public function bebanDosenTerbanyak(
        int $limit = 5,
        array $prodiIds = []
    ): Collection {
        $assignments = PembimbingAkademik::query()
            ->where(
                'jenis',
                PembimbingAkademikJenis::DOSEN_WALI
            )
            ->where(
                'status',
                PembimbingAkademikStatus::AKTIF
            )
            ->when(
                $prodiIds !== [],
                function (Builder $query) use ($prodiIds) {
                    $query->where(function (Builder $q) use ($prodiIds) {
                        $q
                            ->whereHas(
                                'kelas',
                                fn(Builder $kelas) => $kelas
                                    ->whereIn(
                                        'prodi_id',
                                        $prodiIds
                                    )
                            )
                            ->orWhereHas(
                                'mahasiswa',
                                fn(Builder $mahasiswa) => $mahasiswa
                                    ->whereIn(
                                        'prodi_id',
                                        $prodiIds
                                    )
                            );
                    });
                }
            )
            ->with([
                'dosen',
                'kelas',
            ])
            ->get();

        if ($assignments->isEmpty()) {
            return collect();
        }

        /*
         * Ambil semua kelas yang terlibat.
         */
        $kelasIds = $assignments
            ->whereNotNull('kelas_id')
            ->pluck('kelas_id')
            ->unique()
            ->values();

        /*
         * Hitung jumlah mahasiswa aktif per kelas SEKALI.
         */
        $jumlahMahasiswaPerKelas = $kelasIds->isEmpty()
            ? collect()
            : DB::table('mahasiswa_kelas')
            ->select(
                'kelas_id',
                DB::raw('COUNT(*) as total')
            )
            ->whereIn('kelas_id', $kelasIds)
            ->whereNull('tanggal_keluar')
            ->groupBy('kelas_id')
            ->pluck('total', 'kelas_id');

        /*
         * Group berdasarkan dosen.
         */
        return $assignments
            ->groupBy('dosen_id')
            ->map(
                function (Collection $rows) use (
                    $jumlahMahasiswaPerKelas
                ) {
                    $total = 0;

                    foreach ($rows as $assignment) {
                        /*
                         * Assignment langsung ke mahasiswa.
                         */
                        if ($assignment->mahasiswa_id !== null) {
                            $total++;

                            continue;
                        }

                        /*
                         * Assignment ke kelas.
                         */
                        if ($assignment->kelas_id !== null) {
                            $total += (int) (
                                $jumlahMahasiswaPerKelas[$assignment->kelas_id] ?? 0
                            );
                        }
                    }

                    return [
                        'dosen' => $rows->first()->dosen,
                        'total' => $total,
                    ];
                }
            )
            ->sortByDesc('total')
            ->take($limit)
            ->values();
    }

    /**
     * Statistik utama monitoring Pembimbing Akademik.
     *
     * Semua statistik dibatasi berdasarkan Program Studi
     * yang boleh diakses user.
     *
     * @return array{
     *     total_mahasiswa_aktif:int,
     *     mahasiswa_sudah_punya_wali:int,
     *     mahasiswa_belum_punya_wali:int,
     *     dosen_wali_aktif:int,
     *     dosen_beban_tinggi:int,
     *     assignment_berakhir:int,
     *     kelas_per_kelas:int,
     *     kelas_dengan_wali:int,
     *     persentase_kelas_dengan_wali:float
     * }
     */
    public function monitoringStats(
        array $prodiIds
    ): array {
        if ($prodiIds === []) {
            return [
                'total_mahasiswa_aktif' => 0,
                'mahasiswa_sudah_punya_wali' => 0,
                'mahasiswa_belum_punya_wali' => 0,
                'dosen_wali_aktif' => 0,
                'dosen_beban_tinggi' => 0,
                'assignment_berakhir' => 0,
                'kelas_per_kelas' => 0,
                'kelas_dengan_wali' => 0,
                'persentase_kelas_dengan_wali' => 0.0,
            ];
        }

        /*
         * ================================================================
         * 1. TOTAL MAHASISWA AKTIF
         * ================================================================
         */

        $mahasiswaAktif = Mahasiswa::query()
            ->whereNull('deleted_at')
            ->whereIn('prodi_id', $prodiIds);

        $totalMahasiswa = (clone $mahasiswaAktif)
            ->count();

        /*
         * ================================================================
         * 2. MAHASISWA SUDAH PUNYA WALI
         * ================================================================
         *
         * Menggunakan definisi yang sama dengan
         * totalSudahPunyaWali() dan queryMahasiswaTanpaWali().
         */

        $mahasiswaSudahPunyaWali = (clone $mahasiswaAktif)
            ->where(function (Builder $query) {
                $query
                    ->whereHas(
                        'pembimbingAkademik',
                        fn(Builder $q) => $q
                            ->where(
                                'jenis',
                                PembimbingAkademikJenis::DOSEN_WALI
                            )
                            ->where(
                                'status',
                                PembimbingAkademikStatus::AKTIF
                            )
                    )
                    ->orWhereHas(
                        'mahasiswaKelas',
                        function (Builder $q) {
                            $q
                                ->whereNull('tanggal_keluar')
                                ->whereHas(
                                    'kelas',
                                    function (Builder $kelas) {
                                        $kelas->whereHas(
                                            'pembimbingAkademik',
                                            fn(Builder $wali) => $wali
                                                ->where(
                                                    'jenis',
                                                    PembimbingAkademikJenis::DOSEN_WALI
                                                )
                                                ->where(
                                                    'status',
                                                    PembimbingAkademikStatus::AKTIF
                                                )
                                        );
                                    }
                                );
                        }
                    );
            })
            ->count();

        /*
         * Safety guard.
         */
        $mahasiswaSudahPunyaWali = min(
            $mahasiswaSudahPunyaWali,
            $totalMahasiswa
        );

        $mahasiswaBelumPunyaWali = max(
            0,
            $totalMahasiswa - $mahasiswaSudahPunyaWali
        );

        /*
         * ================================================================
         * 3. DOSEN WALI AKTIF
         * ================================================================
         */

        $dosenWaliAktif = PembimbingAkademik::query()
            ->where(
                'jenis',
                PembimbingAkademikJenis::DOSEN_WALI
            )
            ->where(
                'status',
                PembimbingAkademikStatus::AKTIF
            )
            ->where(function (Builder $query) use ($prodiIds) {
                $query
                    ->whereHas(
                        'kelas',
                        fn(Builder $kelas) => $kelas
                            ->whereIn(
                                'prodi_id',
                                $prodiIds
                            )
                    )
                    ->orWhereHas(
                        'mahasiswa',
                        fn(Builder $mahasiswa) => $mahasiswa
                            ->whereIn(
                                'prodi_id',
                                $prodiIds
                            )
                    );
            })
            ->distinct()
            ->count('dosen_id');

        /*
         * ================================================================
         * 4. ASSIGNMENT YANG SUDAH BERAKHIR
         * ================================================================
         *
         * Masih AKTIF tetapi tanggal_selesai sudah lewat.
         */

        $assignmentBerakhir = PembimbingAkademik::query()
            ->where(
                'jenis',
                PembimbingAkademikJenis::DOSEN_WALI
            )
            ->where(
                'status',
                PembimbingAkademikStatus::AKTIF
            )
            ->whereNotNull('tanggal_selesai')
            ->whereDate(
                'tanggal_selesai',
                '<',
                now()->toDateString()
            )
            ->where(function (Builder $query) use ($prodiIds) {
                $query
                    ->whereHas(
                        'kelas',
                        fn(Builder $kelas) => $kelas
                            ->whereIn(
                                'prodi_id',
                                $prodiIds
                            )
                    )
                    ->orWhereHas(
                        'mahasiswa',
                        fn(Builder $mahasiswa) => $mahasiswa
                            ->whereIn(
                                'prodi_id',
                                $prodiIds
                            )
                    );
            })
            ->count();

        /*
         * ================================================================
         * 5. KELAS MODE PER_KELAS
         * ================================================================
         */

        $kelasPerKelasQuery = DB::table('kelas')
            ->join(
                'konfigurasi_pembimbing_akademik as konfigurasi',
                function ($join) {
                    $join
                        ->on(
                            'konfigurasi.prodi_id',
                            '=',
                            'kelas.prodi_id'
                        )
                        ->on(
                            'konfigurasi.angkatan_id',
                            '=',
                            'kelas.angkatan_id'
                        );
                }
            )
            ->whereIn(
                'kelas.prodi_id',
                $prodiIds
            )
            ->where(
                'konfigurasi.aktif',
                true
            )
            ->where(
                'konfigurasi.mode',
                PembimbingAkademikMode::PER_KELAS->value
            );

        $kelasPerKelas = (clone $kelasPerKelasQuery)
            ->count();

        /*
         * ================================================================
         * 6. KELAS YANG SUDAH PUNYA WALI
         * ================================================================
         */

        $kelasDenganWali = (clone $kelasPerKelasQuery)
            ->whereExists(
                function ($query) {
                    $query
                        ->select(DB::raw(1))
                        ->from('pembimbing_akademik')
                        ->whereColumn(
                            'pembimbing_akademik.kelas_id',
                            'kelas.id'
                        )
                        ->where(
                            'pembimbing_akademik.jenis',
                            PembimbingAkademikJenis::DOSEN_WALI->value
                        )
                        ->where(
                            'pembimbing_akademik.status',
                            PembimbingAkademikStatus::AKTIF->value
                        );
                }
            )
            ->count();

        $persentaseKelasDenganWali = $kelasPerKelas > 0
            ? round(
                ($kelasDenganWali / $kelasPerKelas) * 100,
                1
            )
            : 0.0;

        /*
         * ================================================================
         * 7. BEBAN DOSEN TINGGI
         * ================================================================
         *
         * Threshold: > 40 mahasiswa.
         */

        $bebanDosen = $this->bebanDosenTerbanyak(
            limit: PHP_INT_MAX,
            prodiIds: $prodiIds,
        );

        $dosenBebanTinggi = $bebanDosen
            ->where('total', '>', 40)
            ->count();

        /*
         * ================================================================
         * RETURN
         * ================================================================
         */

        return [
            'total_mahasiswa_aktif' =>
            $totalMahasiswa,

            'mahasiswa_sudah_punya_wali' =>
            $mahasiswaSudahPunyaWali,

            'mahasiswa_belum_punya_wali' =>
            $mahasiswaBelumPunyaWali,

            'dosen_wali_aktif' =>
            $dosenWaliAktif,

            'dosen_beban_tinggi' =>
            $dosenBebanTinggi,

            'assignment_berakhir' =>
            $assignmentBerakhir,

            'kelas_per_kelas' =>
            $kelasPerKelas,

            'kelas_dengan_wali' =>
            $kelasDenganWali,

            'persentase_kelas_dengan_wali' =>
            $persentaseKelasDenganWali,
        ];
    }
}
