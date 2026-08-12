<?php

namespace App\Services;

use App\Enums\PembimbingAkademikJenis;
use App\Enums\PembimbingAkademikMode;
use App\Enums\PembimbingAkademikStatus;
use App\Exceptions\PembimbingAkademikException;
use App\Models\Dosen;
use App\Models\KonfigurasiPembimbingAkademik;
use App\Models\Mahasiswa;
use App\Models\PembimbingAkademik;
use App\Models\TrxDosen;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Semua aturan bisnis modul Pembimbing Akademik hidup di sini — bukan di
 * dalam Page/Resource Filament. Tujuannya:
 *  - Page/Resource cukup memanggil service, tidak perlu tahu detail query.
 *  - Aturan (mis. "hanya Dosen Wali yang boleh per kelas") jadi satu sumber
 *    kebenaran, tidak terduplikasi di beberapa halaman.
 *  - Bisa diuji (unit test) tanpa perlu render Livewire component.
 */
class PembimbingAkademikService
{
    /**
     * Cari konfigurasi mode yang AKTIF untuk kombinasi prodi + angkatan.
     * Null berarti belum diatur / sedang dinonaktifkan.
     */
    public function konfigurasiAktif(?int $prodiId, ?int $angkatanId): ?KonfigurasiPembimbingAkademik
    {
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
     * Aturan bisnis: hanya Dosen Wali yang bisa ditetapkan per kelas
     * (mengikuti konfigurasi). Jenis lain (pembimbing skripsi/tesis/PKL/dst)
     * secara alami selalu per mahasiswa karena sifatnya individual.
     */
    public function modeUntuk(PembimbingAkademikJenis $jenis, ?KonfigurasiPembimbingAkademik $konfigurasi): ?PembimbingAkademikMode
    {
        if ($jenis !== PembimbingAkademikJenis::DOSEN_WALI) {
            return PembimbingAkademikMode::PER_MAHASISWA;
        }

        // SENGAJA null (bukan fallback ke PER_MAHASISWA) kalau konfigurasi
        // belum aktif — supaya semua pemanggil (UI & tugaskan()) WAJIB
        // menangani kasus "belum ada mode", bukan diam-diam meloloskan
        // penugasan lewat jalur per-mahasiswa tanpa konfigurasi sama sekali.
        return $konfigurasi?->mode;
    }

    public function sudahPunyaPembimbingAktif(PembimbingAkademikJenis $jenis, ?int $kelasId, ?string $mahasiswaId): bool
    {
        return PembimbingAkademik::query()
            ->where('jenis', $jenis)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->when($kelasId, fn(Builder $q) => $q->where('kelas_id', $kelasId))
            ->when($mahasiswaId, fn(Builder $q) => $q->where('mahasiswa_id', $mahasiswaId))
            ->exists();
    }

    /**
     * Kelas yang belum punya Dosen Wali aktif untuk prodi + angkatan tertentu.
     * Dipakai untuk menyaring pilihan di form Penugasan (UX: user tidak
     * disodori kelas yang sebenarnya sudah punya wali).
     */
    public function kelasBelumPunyaWali(int $prodiId, int $angkatanId): Collection
    {
        return \App\Models\Kelas::query()
            ->where('prodi_id', $prodiId)
            ->where('angkatan_id', $angkatanId)
            ->whereNotIn('id', $this->kelasIdDenganWaliAktif())
            ->orderBy('nama_kelas')
            ->pluck('nama_kelas', 'id');
    }

    protected function kelasIdDenganWaliAktif(): Collection
    {
        return PembimbingAkademik::query()
            ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->whereNotNull('kelas_id')
            ->pluck('kelas_id');
    }

    /**
     * Buat penugasan pembimbing baru. Melempar PembimbingAkademikException
     * bila aturan bisnis dilanggar — dibungkus transaksi agar atomik.
     *
     * @param  array{
     *     jenis: string,
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
     *     angkatan_id?: int|null,
     * }  $data
     */
    public function tugaskan(array $data): PembimbingAkademik
    {
        $jenis = $data['jenis'] instanceof PembimbingAkademikJenis
            ? $data['jenis']
            : PembimbingAkademikJenis::from($data['jenis']);

        $kelasId = $data['kelas_id'] ?? null;
        $mahasiswaId = $data['mahasiswa_id'] ?? null;

        if (! $kelasId && ! $mahasiswaId) {
            throw PembimbingAkademikException::targetKosong();
        }

        // Validasi ulang di server (defense in depth, TIDAK BOLEH dilewati
        // lewat manipulasi request/UI): Dosen Wali WAJIB punya konfigurasi
        // mode aktif untuk kombinasi prodi+angkatan-nya, apa pun target-nya
        // (kelas ATAU mahasiswa) — sebelumnya celah ini hanya dicek saat
        // targetnya kelas, sehingga jalur mahasiswa lolos tanpa konfigurasi.
        if ($jenis === PembimbingAkademikJenis::DOSEN_WALI) {
            $konfigurasi = $this->konfigurasiAktif($data['prodi_id'] ?? null, $data['angkatan_id'] ?? null);
            $modeEfektif = $this->modeUntuk($jenis, $konfigurasi);

            if (! $modeEfektif) {
                throw PembimbingAkademikException::konfigurasiBelumDiatur();
            }

            if ($modeEfektif === PembimbingAkademikMode::PER_KELAS && ! $kelasId) {
                throw PembimbingAkademikException::konfigurasiBelumDiatur();
            }

            if ($modeEfektif === PembimbingAkademikMode::PER_MAHASISWA && ! $mahasiswaId) {
                throw PembimbingAkademikException::konfigurasiBelumDiatur();
            }
        }

        if ($this->sudahPunyaPembimbingAktif($jenis, $kelasId, $mahasiswaId)) {
            throw PembimbingAkademikException::sudahAdaPembimbingAktif();
        }

        // Jaga-jaga terhadap sisa data dari perubahan mode konfigurasi:
        // jangan sampai mahasiswa yang kelasnya masih punya wali aktif
        // (dari era mode PER_KELAS) diberi wali individual lagi.
        if ($jenis === PembimbingAkademikJenis::DOSEN_WALI && $mahasiswaId && $this->kelasMahasiswaSudahPunyaWali($mahasiswaId)) {
            throw PembimbingAkademikException::sudahDicoverWaliKelas();
        }

        return DB::transaction(fn() => PembimbingAkademik::create([
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
        ]));
    }

    /**
     * Mutasi (ganti dosen) untuk sebuah penugasan yang sedang aktif.
     * Record lama ditutup (SELESAI) dan record baru dibuat — histori utuh.
     *
     * @param  array{dosen_id: string, tanggal_mulai: string, semester_mulai_id: int, nomor_sk?: string|null, tanggal_sk?: string|null, alasan: string}  $data
     */
    public function mutasi(PembimbingAkademik $record, array $data): PembimbingAkademik
    {
        if ((string) $data['dosen_id'] === (string) $record->dosen_id) {
            throw PembimbingAkademikException::dosenPenggantiSama();
        }

        if ($record->tanggal_mulai && (string) $data['tanggal_mulai'] < $record->tanggal_mulai->toDateString()) {
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
     * Batalkan penugasan aktif tanpa menggantinya dengan yang baru.
     */
    public function batalkan(PembimbingAkademik $record, string $alasan): PembimbingAkademik
    {
        $record->update([
            'status' => PembimbingAkademikStatus::DIBATALKAN,
            'tanggal_selesai' => now()->toDateString(),
            'alasan' => $alasan,
            'updated_by' => auth()->id(),
        ]);

        return $record;
    }

    /**
     * Cek apakah kelas dari seorang mahasiswa masih memiliki Dosen Wali
     * aktif tingkat kelas — dipakai untuk mencegah duplikasi saat mode
     * konfigurasi berubah dari PER_KELAS ke PER_MAHASISWA di tengah jalan.
     */
    protected function kelasMahasiswaSudahPunyaWali(string $mahasiswaId): bool
    {
        // Relasi mahasiswa <-> kelas lewat tabel pivot `mahasiswa_kelas`
        // (bukan kolom langsung di `mahasiswas`). tanggal_keluar null berarti
        // keanggotaan kelas tersebut masih berlaku saat ini.
        $kelasIds = DB::table('mahasiswa_kelas')
            ->where('mahasiswa_id', $mahasiswaId)
            ->whereNull('tanggal_keluar')
            ->pluck('kelas_id');

        if ($kelasIds->isEmpty()) {
            return false;
        }

        return PembimbingAkademik::query()
            ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->whereIn('kelas_id', $kelasIds)
            ->exists();
    }

    /**
     * Total penugasan Dosen Wali AKTIF yang "bergantung" pada kombinasi
     * prodi + angkatan tertentu (baik lewat kelas maupun mahasiswa).
     * Dipakai untuk menampilkan peringatan dampak sebelum admin
     * mengubah/menghapus KonfigurasiPembimbingAkademik.
     */
    public function totalPenugasanAktifUntukKombinasi(?int $prodiId, $angkatanId): int
    {
        if (! $prodiId || ! $angkatanId) {
            return 0;
        }

        return PembimbingAkademik::query()
            ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->where(fn(Builder $q) => $q
                ->whereHas('kelas', fn(Builder $k) => $k->where('prodi_id', $prodiId)->where('angkatan_id', $angkatanId))
                ->orWhereHas('mahasiswa', fn(Builder $m) => $m->where('prodi_id', $prodiId)->where('angkatan_id', $angkatanId)))
            ->count();
    }

    public function totalMahasiswaAktif(): int
    {
        return Mahasiswa::query()->whereNull('deleted_at')->count();
    }

    public function totalSudahPunyaWali(): int
    {
        return Mahasiswa::query()
            ->whereNull('deleted_at')
            ->whereHas('pembimbingAkademik', fn(Builder $q) => $q
                ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
                ->where('status', PembimbingAkademikStatus::AKTIF))
            ->count();
    }

    public function totalBelumPunyaWali(): int
    {
        return $this->totalMahasiswaAktif() - $this->totalSudahPunyaWali();
    }

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
     * Beban dihitung berdasarkan mahasiswa yang benar-benar tercakup:
     *
     * - assignment PER_MAHASISWA -> 1 assignment = 1 mahasiswa
     * - assignment PER_KELAS     -> jumlah mahasiswa aktif dalam kelas
     *
     * Catatan:
     * Query ini sengaja dipisahkan dari monitoringStats()
     * karena halaman monitoring membutuhkan ranking detail.
     *
     * @return Collection<int, array{dosen: TrxDosen, total: int}>
     */
    public function bebanDosenTerbanyak(
        int $limit = 5,
        array $prodiIds = [],
    ): Collection {
        /*
     * Ambil assignment Dosen Wali aktif.
     */
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
                                fn(Builder $kelas) =>
                                $kelas->whereIn(
                                    'prodi_id',
                                    $prodiIds
                                )
                            )
                            ->orWhereHas(
                                'mahasiswa',
                                fn(Builder $mahasiswa) =>
                                $mahasiswa->whereIn(
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

        $kelasIds = $assignments
            ->whereNotNull('kelas_id')
            ->pluck('kelas_id')
            ->unique()
            ->values();

        /*
     * Hitung mahasiswa aktif per kelas SEKALI saja.
     *
     * Jangan panggil jumlah anggota kelas dalam foreach.
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

        return $assignments
            ->groupBy('dosen_id')
            ->map(function (Collection $rows) use (
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
            })
            ->sortByDesc('total')
            ->take($limit)
            ->values();
    }


    /**
     * Statistik utama monitoring Pembimbing Akademik.
     *
     * Semua statistik sudah dibatasi berdasarkan Program Studi
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
        array $prodiIds,
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
                'persentase_kelas_dengan_wali' => 100.0,
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
     * 2. MAHASISWA SUDAH PUNYA DOSEN WALI
     * ================================================================
     *
     * PER_MAHASISWA:
     * langsung punya assignment mahasiswa_id.
     *
     * PER_KELAS:
     * mahasiswa dianggap sudah memiliki wali apabila kelas
     * aktifnya memiliki assignment Dosen Wali aktif.
     *
     * Ini penting.
     *
     * Jangan hanya:
     *
     * whereHas('pembimbingAkademik')
     *
     * karena mahasiswa dalam mode PER_KELAS tidak mempunyai
     * baris pembimbing_akademik langsung.
     */

        $mahasiswaDenganWaliIndividual = Mahasiswa::query()
            ->whereNull('deleted_at')
            ->whereIn('prodi_id', $prodiIds)
            ->whereHas(
                'pembimbingAkademik',
                fn(Builder $query) =>
                $query
                    ->where(
                        'jenis',
                        PembimbingAkademikJenis::DOSEN_WALI
                    )
                    ->where(
                        'status',
                        PembimbingAkademikStatus::AKTIF
                    )
            )
            ->count();

        /*
     * Mahasiswa yang kelas aktifnya mempunyai wali.
     */
        $mahasiswaDenganWaliKelas = Mahasiswa::query()
            ->whereNull('deleted_at')
            ->whereIn('prodi_id', $prodiIds)
            ->whereDoesntHave(
                'pembimbingAkademik',
                fn(Builder $query) =>
                $query
                    ->where(
                        'jenis',
                        PembimbingAkademikJenis::DOSEN_WALI
                    )
                    ->where(
                        'status',
                        PembimbingAkademikStatus::AKTIF
                    )
            )
            ->whereHas(
                'mahasiswaKelas',
                function (Builder $query) {
                    $query
                        ->whereNull('tanggal_keluar')
                        ->whereHas(
                            'kelas',
                            fn(Builder $kelas) =>
                            $kelas->whereHas(
                                'pembimbingAkademik',
                                fn(Builder $wali) =>
                                $wali
                                    ->where(
                                        'jenis',
                                        PembimbingAkademikJenis::DOSEN_WALI
                                    )
                                    ->where(
                                        'status',
                                        PembimbingAkademikStatus::AKTIF
                                    )
                            )
                        );
                }
            )
            ->count();

        $mahasiswaSudahPunyaWali =
            $mahasiswaDenganWaliIndividual
            + $mahasiswaDenganWaliKelas;

        /*
     * Hindari kemungkinan double count.
     */
        $mahasiswaSudahPunyaWali = min(
            $mahasiswaSudahPunyaWali,
            $totalMahasiswa
        );

        $mahasiswaBelumPunyaWali =
            max(
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
                        fn(Builder $kelas) =>
                        $kelas->whereIn(
                            'prodi_id',
                            $prodiIds
                        )
                    )
                    ->orWhereHas(
                        'mahasiswa',
                        fn(Builder $mahasiswa) =>
                        $mahasiswa->whereIn(
                            'prodi_id',
                            $prodiIds
                        )
                    );
            })
            ->distinct('dosen_id')
            ->count('dosen_id');

        /*
     * ================================================================
     * 4. ASSIGNMENT YANG SUDAH BERAKHIR
     * ================================================================
     *
     * Hanya assignment yang secara database masih AKTIF,
     * tetapi tanggal_selesai sudah lewat.
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
                        fn(Builder $kelas) =>
                        $kelas->whereIn(
                            'prodi_id',
                            $prodiIds
                        )
                    )
                    ->orWhereHas(
                        'mahasiswa',
                        fn(Builder $mahasiswa) =>
                        $mahasiswa->whereIn(
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
     *
     * Hanya kelas yang memang konfigurasi pembimbingnya
     * menggunakan PER_KELAS.
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
                        ->from(
                            'pembimbing_akademik'
                        )
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

        $persentaseKelasDenganWali =
            $kelasPerKelas > 0
            ? round(
                ($kelasDenganWali / $kelasPerKelas) * 100,
                1
            )
            : 100.0;

        /*
     * ================================================================
     * 7. BEBAN DOSEN TINGGI
     * ================================================================
     *
     * Threshold awal: > 40 mahasiswa.
     *
     * Jangan menganggap jumlah assignment = jumlah mahasiswa.
     * Assignment PER_KELAS harus dikonversi menjadi jumlah
     * mahasiswa aktif dalam kelas.
     */

        $bebanDosen = $this->bebanDosenTerbanyak(
            limit: PHP_INT_MAX,
            prodiIds: $prodiIds,
        );

        $dosenBebanTinggi = $bebanDosen
            ->where(
                'total',
                '>',
                40
            )
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
