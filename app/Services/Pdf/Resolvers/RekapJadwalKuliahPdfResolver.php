<?php

declare(strict_types=1);

namespace App\Services\Pdf\Resolvers;

use App\Contracts\Pdf\PdfDataResolverInterface;
use App\DataTransferObjects\Pdf\RekapJadwalKuliahPdfData;
use App\Models\JadwalKuliah;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

class RekapJadwalKuliahPdfResolver implements PdfDataResolverInterface
{
    public function resolve(array $context): RekapJadwalKuliahPdfData
    {
        $tahunAkademikId = $context['tahun_akademik_id'] ?? null;

        if (! $tahunAkademikId) {
            throw new RuntimeException(
                'Context [tahun_akademik_id] wajib diisi.'
            );
        }

        $rows = $this->query($context)
            ->get()
            ->map(fn(JadwalKuliah $jadwal) => $this->mapRow($jadwal))
            ->values();

        $totalDosen = $rows
            ->flatMap(fn(array $row) => $row['dosen_ids'] ?? [])
            ->filter()
            ->unique()
            ->count();

        return new RekapJadwalKuliahPdfData(
            tahunAkademikId: (int) $tahunAkademikId,

            judulDokumen: 'Rekap Jadwal Kuliah',

            infoBaris: $this->buildInfoBaris($context),

            rows: $rows->all(),

            totalJadwal: $rows->count(),

            totalKelas: $rows
                ->pluck('prodi_semester_kelas')
                ->filter()
                ->unique()
                ->count(),

            totalDosen: $totalDosen,

            totalRuang: $rows
                ->pluck('ruang')
                ->filter(fn($ruang) => filled($ruang) && $ruang !== '-')
                ->unique()
                ->count(),

            hariAktif: $rows
                ->pluck('hari')
                ->filter()
                ->unique()
                ->count(),

            dicetakPada: now()->translatedFormat('d F Y H:i'),
        );
    }

    protected function query(array $context): Builder
    {
        return JadwalKuliah::query()
            ->with([
                'tahunAkademik',
                'mataKuliah',
                'ruang',
                'kelas.prodi.fakultas',
                'dosenPengajars.dosen.person.gelars',
            ])

            ->where(
                'tahun_akademik_id',
                $context['tahun_akademik_id']
            )

            ->when(
                $context['fakultas_id'] ?? null,
                fn(Builder $query, $value) =>
                $query->whereHas(
                    'kelas.prodi',
                    fn(Builder $q) =>
                    $q->where('fakultas_id', $value)
                )
            )

            ->when(
                $context['prodi_id'] ?? null,
                fn(Builder $query, $value) =>
                $query->whereHas(
                    'kelas',
                    fn(Builder $q) =>
                    $q->where('prodi_id', $value)
                )
            )

            ->when(
                $context['dosen_id'] ?? null,
                fn(Builder $query, $value) =>
                $query->whereHas(
                    'dosenPengajars',
                    fn(Builder $q) =>
                    $q->where('dosen_id', $value)
                )
            )

            ->when(
                $context['mata_kuliah_id'] ?? null,
                fn(Builder $query, $value) =>
                $query->where(
                    'mata_kuliah_id',
                    $value
                )
            )

            ->when(
                $context['ruang_id'] ?? null,
                fn(Builder $query, $value) =>
                $query->where(
                    'ruang_id',
                    $value
                )
            )

            ->orderByRaw("
                FIELD(
                    hari,
                    'Senin',
                    'Selasa',
                    'Rabu',
                    'Kamis',
                    'Jumat',
                    'Sabtu',
                    'Minggu'
                )
            ")

            ->orderBy('jam_mulai');
    }

    protected function mapRow(JadwalKuliah $jadwal): array
    {
        $angkatan = $jadwal->kelas?->angkatan_id;
        $kodeTahun = $jadwal->tahunAkademik?->kode_tahun;

        $semester = $this->hitungSemester(
            (int) $angkatan,
            $kodeTahun
        );

        $dosenIds = $jadwal->dosenPengajars
            ->pluck('dosen_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $dosen = $jadwal->dosenPengajars
            ->map(
                fn($dosenPengajar) =>
                $dosenPengajar->dosen?->person?->nama_dengan_gelar
            )
            ->filter()
            ->implode(', ');

        return [
            'hari' => $jadwal->hari,

            'jam_mulai' => $jadwal->jam_mulai
                ? substr($jadwal->jam_mulai, 0, 5)
                : '-',

            'jam_selesai' => $jadwal->jam_selesai
                ? substr($jadwal->jam_selesai, 0, 5)
                : '-',

            'kode_mk' => $jadwal->mataKuliah?->kode_mk ?? '-',

            'nama_mk' => $jadwal->mataKuliah?->nama_mk ?? '-',

            'sks' => $jadwal->mataKuliah?->sks_default ?? '-',

            'dosen' => $dosen ?: '-',

            /*
             * Dipakai resolver untuk menghitung total dosen unik.
             * Tidak perlu ditampilkan di Blade.
             */
            'dosen_ids' => $dosenIds,

            'prodi_kode' =>
            $jadwal->kelas?->prodi?->kode_prodi_internal ?? '-',

            'semester' => $semester ?? '-',

            'ruang' =>
            $jadwal->ruang?->nama_ruang ?? '-',

            'kelas' =>
            $jadwal->kelas?->nama_kelas ?? '-',

            'angkatan' =>
            $angkatan ?? '-',

            'prodi_semester_kelas' => sprintf(
                '%s/%s/%s',
                $jadwal->kelas?->prodi?->kode_prodi_internal ?? '-',
                $semester ?? '-',
                $jadwal->kelas?->nama_kelas ?? '-',
            ),
        ];
    }

    protected function hitungSemester(
        int $angkatan,
        ?string $kodeTahun
    ): ?int {
        if (! $angkatan || ! $kodeTahun) {
            return null;
        }

        if (! preg_match(
            '/^(\d{4})([123])$/',
            $kodeTahun,
            $matches
        )) {
            return null;
        }

        $tahunMulai = (int) $matches[1];
        $periode = (int) $matches[2];

        /*
         * Periode 3 = Semester Pendek.
         * Tidak dihitung sebagai semester reguler mahasiswa.
         */
        if ($periode === 3) {
            return null;
        }

        $selisihTahun = $tahunMulai - $angkatan;

        if ($selisihTahun < 0) {
            return null;
        }

        return ($selisihTahun * 2) + $periode;
    }

    protected function buildInfoBaris(array $context): array
    {
        $info = [];

        /*
     * ============================================================
     * TAHUN AKADEMIK
     * ============================================================
     */

        if (! empty($context['tahun_akademik_id'])) {
            $jadwal = JadwalKuliah::query()
                ->with('tahunAkademik')
                ->where('tahun_akademik_id', $context['tahun_akademik_id'])
                ->first();

            if ($jadwal?->tahunAkademik) {
                $tahun = $jadwal->tahunAkademik;

                $label = $tahun->nama
                    ?? $tahun->nama_tahun
                    ?? $tahun->kode_tahun
                    ?? (string) $context['tahun_akademik_id'];

                $info[] = 'Tahun Akademik: ' . $label;
            } else {
                $info[] = 'Tahun Akademik: ' . $context['tahun_akademik_id'];
            }
        }


        /*
     * ============================================================
     * FAKULTAS
     * ============================================================
     *
     * Kita ambil dari jadwal yang sudah memiliki relasi:
     * kelas -> prodi -> fakultas
     */

        if (! empty($context['fakultas_id'])) {

            $jadwal = $this->query([
                'tahun_akademik_id' => $context['tahun_akademik_id'],
                'fakultas_id' => $context['fakultas_id'],
            ])
                ->with('kelas.prodi.fakultas')
                ->first();

            $fakultas = $jadwal?->kelas?->prodi?->fakultas;

            if ($fakultas) {
                $label = $fakultas->nama_fakultas
                    ?? $fakultas->nama
                    ?? ('ID ' . $context['fakultas_id']);

                $info[] = 'Fakultas: ' . $label;
            } else {
                $info[] = 'Fakultas ID: ' . $context['fakultas_id'];
            }
        }


        /*
     * ============================================================
     * PRODI
     * ============================================================
     */

        if (! empty($context['prodi_id'])) {

            $jadwal = $this->query([
                'tahun_akademik_id' => $context['tahun_akademik_id'],
                'prodi_id' => $context['prodi_id'],
            ])
                ->with('kelas.prodi')
                ->first();

            $prodi = $jadwal?->kelas?->prodi;

            if ($prodi) {

                $kode = $prodi->kode_prodi_internal
                    ?? $prodi->kode_prodi
                    ?? null;

                $nama = $prodi->nama_prodi
                    ?? $prodi->nama
                    ?? null;

                $label = collect([$kode, $nama])
                    ->filter()
                    ->implode(' - ');

                $info[] = 'Prodi: ' . ($label ?: 'ID ' . $context['prodi_id']);
            } else {
                $info[] = 'Prodi ID: ' . $context['prodi_id'];
            }
        }


        /*
     * ============================================================
     * MATA KULIAH
     * ============================================================
     */

        if (! empty($context['mata_kuliah_id'])) {

            $jadwal = $this->query([
                'tahun_akademik_id' => $context['tahun_akademik_id'],
                'mata_kuliah_id' => $context['mata_kuliah_id'],
            ])
                ->with('mataKuliah')
                ->first();

            $mataKuliah = $jadwal?->mataKuliah;

            if ($mataKuliah) {

                $kode = $mataKuliah->kode_mk ?? null;
                $nama = $mataKuliah->nama_mk ?? null;

                $label = collect([$kode, $nama])
                    ->filter()
                    ->implode(' - ');

                $info[] = 'Mata Kuliah: ' . ($label ?: 'ID ' . $context['mata_kuliah_id']);
            } else {
                $info[] = 'Mata Kuliah ID: ' . $context['mata_kuliah_id'];
            }
        }


        /*
     * ============================================================
     * RUANG
     * ============================================================
     */

        if (! empty($context['ruang_id'])) {

            $jadwal = $this->query([
                'tahun_akademik_id' => $context['tahun_akademik_id'],
                'ruang_id' => $context['ruang_id'],
            ])
                ->with('ruang')
                ->first();

            $ruang = $jadwal?->ruang;

            if ($ruang) {

                $label = $ruang->nama_ruang
                    ?? $ruang->nama
                    ?? ('ID ' . $context['ruang_id']);

                $info[] = 'Ruang: ' . $label;
            } else {
                $info[] = 'Ruang ID: ' . $context['ruang_id'];
            }
        }


        return $info;
    }
}
