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
        return [];
    }
}
