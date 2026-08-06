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
            ->whereDoesntHave('pembimbingAkademik', fn(Builder $q) => $q
                ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
                ->where('status', PembimbingAkademikStatus::AKTIF));
    }

    /**
     * Top-N dosen dengan beban bimbingan (Dosen Wali aktif) terbanyak.
     * Berguna di halaman Monitoring untuk melihat distribusi beban.
     *
     * @return Collection<int, array{dosen: Dosen, total: int}>
     */
    public function bebanDosenTerbanyak(int $limit = 5): Collection
    {
        return PembimbingAkademik::query()
            ->where('jenis', PembimbingAkademikJenis::DOSEN_WALI)
            ->where('status', PembimbingAkademikStatus::AKTIF)
            ->selectRaw('dosen_id, count(*) as total')
            ->groupBy('dosen_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'dosen' => TrxDosen::find($row->dosen_id),
                'total' => (int) $row->total,
            ])
            ->filter(fn(array $row) => $row['dosen'] !== null)
            ->values();
    }
}
