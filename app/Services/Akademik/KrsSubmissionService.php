<?php

declare(strict_types=1);

namespace App\Services\Akademik;

use App\Domain\Akademik\Enums\StatusAmbil;
use App\DTOs\KrsSubmissionResult;
use App\Enums\KrsStatusEnum;
use App\Models\JadwalKuliah;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Satu-satunya titik persistensi pengajuan KRS oleh mahasiswa.
 *
 * Aturan:
 *  - Tidak ada KRS            -> buat 1 record baru (DIAJUKAN).
 *  - KRS DRAFT                -> record yang sama, diajukan (DIAJUKAN).
 *  - KRS DITOLAK              -> record yang sama, direvisi lalu diajukan kembali (DIAJUKAN).
 *  - DIAJUKAN/DISETUJUI/DIBATALKAN -> ditolak (DomainException).
 *
 * Catatan:
 *  - `isi_kelas` TIDAK disentuh di sini. Kapasitas hanya berubah saat
 *    approve (+1, KrsApprovalService) dan cancel/buka kembali (-1, KrsTable).
 *  - Validasi akademik (SKS, bentrok, kuota, keuangan, periode) dijalankan
 *    oleh pemanggil sebelum service ini.
 *  - Semua kegagalan aturan bisnis dilempar sebagai DomainException dengan
 *    pesan yang aman ditampilkan kepada mahasiswa.
 */
class KrsSubmissionService
{
    /** Retry transaksi untuk kasus deadlock (gap lock pada submit pertama bersamaan). */
    private const PERCOBAAN_TRANSAKSI = 3;

    private const MYSQL_DUPLICATE_ENTRY = 1062;

    private const MYSQL_FK_PARENT_ROW = 1451;

    public function __construct(
        private readonly PembimbingAkademikResolver $pembimbingAkademikResolver,
    ) {}

    /**
     * KRS mahasiswa pada tahun akademik tertentu (maksimal satu: unique key).
     */
    public function findKrs(string $mahasiswaId, int $tahunAkademikId): ?Krs
    {
        return Krs::withoutGlobalScopes()
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('tahun_akademik_id', $tahunAkademikId)
            ->first();
    }

    /**
     * Alasan penolakan terakhir. Sumber utama: log DITOLAK terbaru
     * (krs_status_logs.catatan); fallback: krs.catatan_admin.
     */
    public function alasanPenolakan(Krs $krs): ?string
    {
        $catatanLog = DB::table('krs_status_logs')
            ->where('krs_id', $krs->getKey())
            ->where('aksi', 'DITOLAK')
            ->orderByDesc('id')
            ->value('catatan');

        if (filled($catatanLog)) {
            return trim((string) $catatanLog);
        }

        return filled($krs->catatan_admin) ? trim((string) $krs->catatan_admin) : null;
    }

    /**
     * Pilihan jadwal yang tersimpan pada KRS, dipisah menurut status_ambil.
     *
     * @return array{utama: list<string>, mengulang: list<string>}
     */
    public function pilihanTersimpan(Krs $krs): array
    {
        $rows = DB::table('krs_detail')
            ->where('krs_id', $krs->getKey())
            ->whereNotNull('jadwal_kuliah_id')
            ->get(['jadwal_kuliah_id', 'status_ambil']);

        $utama = [];
        $mengulang = [];

        foreach ($rows as $row) {
            if ($row->status_ambil === StatusAmbil::ULANG->value) {
                $mengulang[] = (string) $row->jadwal_kuliah_id;

                continue;
            }

            $utama[] = (string) $row->jadwal_kuliah_id;
        }

        return [
            'utama' => $utama,
            'mengulang' => $mengulang,
        ];
    }

    /**
     * Ajukan KRS (baru / dari DRAFT / revisi dari DITOLAK) dalam satu transaksi.
     *
     * @param  array<int, string>  $jadwalUtama
     * @param  array<int, string>  $jadwalMengulang
     *
     * @throws DomainException
     */
    public function ajukan(
        Mahasiswa $mahasiswa,
        RefTahunAkademik $tahunAkademik,
        int $kelasId,
        array $jadwalUtama,
        array $jadwalMengulang,
    ): KrsSubmissionResult {
        $jadwalUtama = $this->normalisasiIds($jadwalUtama);
        $jadwalMengulang = $this->normalisasiIds($jadwalMengulang);
        $jadwalIds = array_values(array_unique(array_merge($jadwalUtama, $jadwalMengulang)));

        if ($jadwalIds === []) {
            throw new DomainException('Silakan pilih minimal satu mata kuliah sebelum mengajukan KRS.');
        }

        try {
            return DB::transaction(
                fn(): KrsSubmissionResult => $this->prosesPengajuan(
                    $mahasiswa,
                    $tahunAkademik,
                    $kelasId,
                    $jadwalUtama,
                    $jadwalMengulang,
                    $jadwalIds,
                ),
                self::PERCOBAAN_TRANSAKSI,
            );
        } catch (QueryException $e) {
            throw $this->petakanQueryException($e) ?? $e;
        }
    }

    /**
     * @param  list<string>  $jadwalUtama
     * @param  list<string>  $jadwalMengulang
     * @param  list<string>  $jadwalIds
     */
    private function prosesPengajuan(
        Mahasiswa $mahasiswa,
        RefTahunAkademik $tahunAkademik,
        int $kelasId,
        array $jadwalUtama,
        array $jadwalMengulang,
        array $jadwalIds,
    ): KrsSubmissionResult {
        // 1. Kunci KRS existing (jika ada) agar submit ganda/serentak terserialisasi.
        $krs = Krs::withoutGlobalScopes()
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->lockForUpdate()
            ->first();

        $statusSebelumnya = $krs?->status_krs;

        if (
            $krs !== null
            && ! in_array($statusSebelumnya, [KrsStatusEnum::DRAFT, KrsStatusEnum::DITOLAK,  KrsStatusEnum::DIBATALKAN,], true)
        ) {
            throw new DomainException($this->pesanStatusTerkunci($statusSebelumnya));
        }

        // 2. Bangun baris detail dari data jadwal di database (bukan dari state client).
        $baris = $this->bangunBarisDetail(
            $tahunAkademik,
            $kelasId,
            $jadwalUtama,
            $jadwalMengulang,
            $jadwalIds,
        );

        $totalSks = (int) array_sum(array_column($baris, 'sks_snapshot'));

        $sekarang = now();
        $isPaket = ($mahasiswa->kurikulum?->mode_krs ?? 'PAKET') === 'PAKET';
        $dosenWaliId = $this->pembimbingAkademikResolver->dosenWaliAktif($mahasiswa)?->dosen_id;

        // 3. Header KRS.
        if ($krs === null) {
            $krsId = (string) Str::uuid();

            DB::table('krs')->insert([
                'id' => $krsId,
                'mahasiswa_id' => $mahasiswa->id,
                'tahun_akademik_id' => $tahunAkademik->id,
                'kelas_id' => $kelasId,
                'dosen_wali_id' => $dosenWaliId,
                'is_paket_snapshot' => $isPaket,
                'diajukan_at' => $sekarang,
                'status_krs' => KrsStatusEnum::DIAJUKAN->value,
                'total_sks_diambil' => $totalSks,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ]);

            $catatan = 'KRS diajukan secara mandiri oleh mahasiswa.';
        } else {
            $krsId = (string) $krs->getKey();

            $atribut = [
                'kelas_id' => $kelasId,
                'dosen_wali_id' => $dosenWaliId ?? $krs->dosen_wali_id,
                'is_paket_snapshot' => $isPaket,
                'status_krs' => KrsStatusEnum::DIAJUKAN,
                'diajukan_at' => $sekarang,
                'total_sks_diambil' => $totalSks,
            ];

            if ($statusSebelumnya === KrsStatusEnum::DITOLAK) {
                // Riwayat penolakan tetap ada di krs_status_logs; kolom ringkasan
                // di-reset agar tidak tampil sebagai catatan hasil review berikutnya.
                $atribut['ditolak_oleh'] = null;
                $atribut['ditolak_pada'] = null;
                $atribut['catatan_admin'] = null;

                $catatan = 'KRS diajukan kembali oleh mahasiswa setelah revisi.';
            } else {
                $catatan = 'KRS diajukan secara mandiri oleh mahasiswa.';
            }

            // Eloquent agar perubahan tercatat pada activity log Spatie.
            $krs->forceFill($atribut)->save();
        }

        // 4. Detail KRS: sinkronisasi berbasis diff (hapus -> update -> insert).
        $this->sinkronkanDetail($krsId, $baris, $sekarang);

        // 5. Audit trail: before_data memakai status aktual hasil lock.
        DB::table('krs_status_logs')->insert([
            'krs_id' => $krsId,
            'aksi' => 'DIAJUKAN',
            'dilakukan_oleh' => Auth::id(),
            'before_data' => $statusSebelumnya !== null
                ? json_encode(['status_krs' => $statusSebelumnya->value], JSON_THROW_ON_ERROR)
                : null,
            'after_data' => json_encode(['status_krs' => KrsStatusEnum::DIAJUKAN->value], JSON_THROW_ON_ERROR),
            'catatan' => $catatan,
            'created_at' => $sekarang,
        ]);

        return new KrsSubmissionResult(
            krsId: $krsId,
            statusSebelumnya: $statusSebelumnya,
            revisi: $statusSebelumnya === KrsStatusEnum::DITOLAK,
            totalSks: $totalSks,
            totalMataKuliah: count($baris),
        );
    }

    /**
     * @param  list<string>  $jadwalUtama
     * @param  list<string>  $jadwalMengulang
     * @param  list<string>  $jadwalIds
     * @return array<string, array<string, mixed>> baris detail, key = jadwal_kuliah_id
     */
    private function bangunBarisDetail(
        RefTahunAkademik $tahunAkademik,
        int $kelasId,
        array $jadwalUtama,
        array $jadwalMengulang,
        array $jadwalIds,
    ): array {
        $jadwals = JadwalKuliah::query()
            ->with('mataKuliah')
            ->whereIn('id', $jadwalIds)
            ->get()
            ->keyBy('id');

        if ($jadwals->count() !== count($jadwalIds)) {
            throw new DomainException('Sebagian jadwal kuliah yang dipilih tidak ditemukan. Muat ulang halaman lalu coba lagi.');
        }

        $baris = [];
        $mataKuliahTerpakai = [];

        foreach ($jadwalIds as $jadwalId) {
            /** @var JadwalKuliah $jadwal */
            $jadwal = $jadwals->get($jadwalId);

            if ((int) $jadwal->tahun_akademik_id !== (int) $tahunAkademik->id) {
                throw new DomainException('Jadwal kuliah yang dipilih tidak termasuk tahun akademik aktif.');
            }

            if (! $jadwal->mataKuliah) {
                throw new DomainException('Data mata kuliah pada jadwal tidak ditemukan.');
            }

            $isMengulang = in_array($jadwalId, $jadwalMengulang, true);
            $isKelasSendiri = (int) $jadwal->kelas_id === $kelasId;

            if (in_array($jadwalId, $jadwalUtama, true) && ! $isKelasSendiri) {
                throw new DomainException('Mata kuliah utama harus berasal dari kelas Anda.');
            }

            if ($isMengulang && $isKelasSendiri) {
                throw new DomainException('Mata kuliah tambahan harus berasal dari kelas lain.');
            }

            if (in_array($jadwal->mata_kuliah_id, $mataKuliahTerpakai, true)) {
                throw new DomainException(
                    "Mata kuliah {$jadwal->mataKuliah->nama_mk} terpilih lebih dari satu kali. Periksa kembali pilihan Anda."
                );
            }

            $mataKuliahTerpakai[] = $jadwal->mata_kuliah_id;

            $baris[$jadwalId] = [
                'jadwal_kuliah_id' => $jadwalId,
                'mata_kuliah_id' => $jadwal->mata_kuliah_id,
                'kode_mk_snapshot' => $jadwal->mataKuliah->kode_mk,
                'nama_mk_snapshot' => $jadwal->mataKuliah->nama_mk,
                'sks_snapshot' => (int) $jadwal->mataKuliah->sks_default,
                'activity_type_snapshot' => $jadwal->activity_type ?? 'REGULAR',
                'status_ambil' => $isMengulang
                    ? StatusAmbil::ULANG->value
                    : StatusAmbil::BARU->value,
            ];
        }

        return $baris;
    }

    /**
     * Samakan krs_detail dengan pilihan terbaru, tanpa membuat ulang baris yang tetap.
     *
     * Urutan WAJIB hapus -> update -> insert: unique key (krs_id, mata_kuliah_id)
     * akan dilanggar bila baris lama untuk MK yang sama belum dihapus saat
     * mahasiswa mengganti jadwal/kelas.
     *
     * @param  array<string, array<string, mixed>>  $baris
     */
    private function sinkronkanDetail(string $krsId, array $baris, \DateTimeInterface $sekarang): void
    {
        $lama = DB::table('krs_detail')->where('krs_id', $krsId)->get();

        $dipertahankan = [];
        $hapusIds = [];

        foreach ($lama as $row) {
            $jadwalId = $row->jadwal_kuliah_id;

            if (
                $jadwalId !== null
                && isset($baris[$jadwalId])
                && (int) $row->mata_kuliah_id === (int) $baris[$jadwalId]['mata_kuliah_id']
            ) {
                $dipertahankan[$jadwalId] = $row;

                continue;
            }

            $hapusIds[] = $row->id;
        }

        // Hapus baris yang dilepas.
        if ($hapusIds !== []) {
            $terkunci = DB::table('krs_detail')
                ->whereIn('id', $hapusIds)
                ->where(function ($query): void {
                    $query->where('is_published', true)
                        ->orWhere('is_locked', true)
                        ->orWhereNotNull('nilai_huruf');
                })
                ->exists();

            if ($terkunci) {
                throw new DomainException(
                    'Sebagian mata kuliah pada KRS ini sudah memiliki nilai sehingga tidak dapat dilepas. Silakan hubungi Admin Prodi.'
                );
            }

            try {
                DB::table('krs_detail')->whereIn('id', $hapusIds)->delete();
            } catch (QueryException $e) {
                if ((int) ($e->errorInfo[1] ?? 0) === self::MYSQL_FK_PARENT_ROW) {
                    throw new DomainException(
                        'Sebagian mata kuliah pada KRS ini sudah memiliki data presensi, nilai, atau ujian sehingga tidak dapat dilepas. Silakan hubungi Admin Prodi.',
                        0,
                        $e,
                    );
                }

                throw $e;
            }
        }

        // Perbarui baris yang dipertahankan bila ada perbedaan.
        foreach ($dipertahankan as $jadwalId => $row) {
            $perubahan = [];

            foreach (['kode_mk_snapshot', 'nama_mk_snapshot', 'sks_snapshot', 'activity_type_snapshot', 'status_ambil'] as $kolom) {
                if ((string) $row->{$kolom} !== (string) $baris[$jadwalId][$kolom]) {
                    $perubahan[$kolom] = $baris[$jadwalId][$kolom];
                }
            }

            if ($perubahan !== []) {
                $perubahan['updated_at'] = $sekarang;

                DB::table('krs_detail')->where('id', $row->id)->update($perubahan);
            }
        }

        // Tambahkan baris baru.
        $baru = [];

        foreach ($baris as $jadwalId => $data) {
            if (isset($dipertahankan[$jadwalId])) {
                continue;
            }

            $baru[] = [
                'krs_id' => $krsId,
                'jadwal_kuliah_id' => $data['jadwal_kuliah_id'],
                'mata_kuliah_id' => $data['mata_kuliah_id'],
                'kode_mk_snapshot' => $data['kode_mk_snapshot'],
                'nama_mk_snapshot' => $data['nama_mk_snapshot'],
                'sks_snapshot' => $data['sks_snapshot'],
                'activity_type_snapshot' => $data['activity_type_snapshot'],
                'status_ambil' => $data['status_ambil'],
                'nilai_angka' => 0,
                'nilai_huruf' => null,
                'nilai_indeks' => 0,
                'is_published' => false,
                'is_locked' => false,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];
        }

        if ($baru !== []) {
            DB::table('krs_detail')->insert($baru);
        }
    }

    private function pesanStatusTerkunci(?KrsStatusEnum $status): string
    {
        return match ($status) {
            KrsStatusEnum::DIAJUKAN => 'KRS Anda sedang menunggu persetujuan Dosen Wali.',
            KrsStatusEnum::DISETUJUI => 'KRS Anda untuk semester ini sudah disetujui.',
            KrsStatusEnum::DIBATALKAN => 'KRS Anda telah dibatalkan.',
            default => 'KRS Anda tidak dapat diubah pada status saat ini.',
        };
    }

    /**
     * Petakan pelanggaran unique key menjadi pesan yang ramah.
     * Mengembalikan null bila bukan kasus yang dikenali (exception asli dilempar ulang).
     */
    private function petakanQueryException(QueryException $e): ?DomainException
    {
        if ((int) ($e->errorInfo[1] ?? 0) !== self::MYSQL_DUPLICATE_ENTRY) {
            return null;
        }

        $pesan = $e->getMessage();

        if (str_contains($pesan, 'krs_mahasiswa_id_tahun_akademik_id_unique')) {
            return new DomainException(
                'KRS Anda untuk tahun akademik ini sudah tercatat. Muat ulang halaman untuk melihat status terbaru.',
                0,
                $e,
            );
        }

        if (str_contains($pesan, 'krs_detail_')) {
            return new DomainException(
                'Terdapat mata kuliah yang terpilih lebih dari satu kali. Periksa kembali pilihan Anda.',
                0,
                $e,
            );
        }

        return null;
    }

    /**
     * @param  array<int|string, mixed>  $ids
     * @return list<string>
     */
    private function normalisasiIds(array $ids): array
    {
        $ids = array_map(static fn($id): string => (string) $id, $ids);
        $ids = array_filter($ids, static fn(string $id): bool => $id !== '');

        return array_values(array_unique($ids));
    }
}
