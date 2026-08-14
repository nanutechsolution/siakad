<?php

declare(strict_types=1);

namespace App\Filament\Mahasiswa\Widgets;

use App\Domain\Akademik\Resolvers\MahasiswaAcademicResolver;
use App\Enums\StatusKuliah;
use App\Models\Mahasiswa;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MahasiswaProfileOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $user = Auth::user();

        if (! $user?->person_id) {
            return [
                Stat::make('Status Akses', 'Akses Ditolak')
                    ->description('Akun Anda tidak terhubung dengan data Mahasiswa.')
                    ->color('danger'),
            ];
        }

        /*
         * ============================================================
         * MAHASISWA
         * ============================================================
         */
        $mahasiswa = Mahasiswa::query()
            ->with([
                'prodi',
                'person',
                'kurikulum',
                'mulaiStudiTahunAkademik',
            ])
            ->where('person_id', $user->person_id)
            ->first();

        if (! $mahasiswa) {
            return [
                Stat::make('Status Akses', 'Akses Ditolak')
                    ->description('Akun Anda tidak terhubung dengan data Mahasiswa.')
                    ->color('danger'),
            ];
        }

        /*
         * ============================================================
         * ACADEMIC RESOLVER
         * ============================================================
         *
         * Semua keputusan akademik utama melalui resolver.
         */
        $academic = app(MahasiswaAcademicResolver::class, [
            'mahasiswa' => $mahasiswa,
        ]);

        $activeTa = $academic->tahunAkademikAktif();

        /*
         * ============================================================
         * AKADEMIK
         * ============================================================
         */

        $ipk = number_format(
            $academic->ipk(),
            2,
            '.',
            ''
        );

        $sksTotal = $academic->sksTotal();

        $semesterMhs = $academic->semesterBerjalan($activeTa);

        $statusKuliah = $academic->statusKuliah();

        $statusLabel = $academic->statusKuliahLabel();

        $statusColor = match ($statusKuliah) {
            StatusKuliah::AKTIF => 'success',
            StatusKuliah::CUTI => 'warning',
            StatusKuliah::LULUS => 'info',
            StatusKuliah::DOUBLE_DEGREE => 'primary',
            null => 'gray',
            default => 'danger',
        };

        /*
         * ============================================================
         * PROGRESS KELULUSAN
         * ============================================================
         */

        $sksLulusSyarat = (int) (
            $mahasiswa->kurikulum?->jumlah_sks_lulus
            ?? 144
        );

        $progressPersen = $sksLulusSyarat > 0
            ? min(
                100,
                round(($sksTotal / $sksLulusSyarat) * 100, 1)
            )
            : 0;

        $progressColor = match (true) {
            $progressPersen >= 100 => 'success',
            $progressPersen >= 75 => 'primary',
            $progressPersen >= 40 => 'warning',
            default => 'gray',
        };

        /*
         * ============================================================
         * STATUS KRS
         * ============================================================
         */

        $krsLabelMap = [
            'DRAFT' => [
                'Draft (Belum Diajukan)',
                'gray',
            ],

            'DIAJUKAN' => [
                'Menunggu Persetujuan Dosen Wali',
                'warning',
            ],

            'DISETUJUI' => [
                'Disetujui',
                'success',
            ],

            'DITOLAK' => [
                'Ditolak — Perlu Direvisi',
                'danger',
            ],

            'DIBATALKAN' => [
                'Dibatalkan',
                'gray',
            ],
        ];

        $statusKrsRaw = $activeTa
            ? DB::table('krs')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $activeTa->id)
            ->value('status_krs')
            : null;

        [$krsLabel, $krsColor] = $statusKrsRaw
            ? (
                $krsLabelMap[$statusKrsRaw]
                ?? [$statusKrsRaw, 'gray']
            )
            : [
                'Belum Mengisi KRS',
                'danger',
            ];

        /*
         * ============================================================
         * KEUANGAN
         * ============================================================
         */

        $tagihanAktif = $activeTa
            ? DB::table('tagihan_mahasiswas')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $activeTa->id)
            ->whereNull('deleted_at')
            ->first()
            : null;

        if (! $tagihanAktif) {
            $keuanganLabel = 'Tagihan Belum Terbit';
            $keuanganColor = 'gray';
            $keuanganDesc =
                'Hubungi bagian Keuangan jika sudah masuk periode KRS.';
        } else {
            $statusBayar = strtoupper(
                $tagihanAktif->status_bayar ?? ''
            );

            $totalTagihan = (float) (
                $tagihanAktif->total_tagihan ?? 0
            );

            $totalBayar = (float) (
                $tagihanAktif->total_bayar ?? 0
            );

            $sisaTagihan = max(
                $totalTagihan - $totalBayar,
                0
            );

            if ($statusBayar === 'LUNAS' || $sisaTagihan <= 0) {
                $keuanganLabel = 'Lunas';
                $keuanganColor = 'success';
                $keuanganDesc =
                    'Tidak ada tunggakan pada semester ini.';
            } else {
                $keuanganLabel = 'Belum Lunas';
                $keuanganColor = 'danger';
                $keuanganDesc =
                    'Sisa tagihan: Rp '
                    . number_format(
                        $sisaTagihan,
                        0,
                        ',',
                        '.'
                    );
            }
        }

        /*
         * ============================================================
         * DISPLAY
         * ============================================================
         */

        return [
            Stat::make(
                'Status Mahasiswa',
                $statusLabel
            )
                ->description(
                    $mahasiswa->nim
                        . ' - '
                        . (
                            $mahasiswa->prodi?->nama_prodi
                            ?? 'Prodi Tidak Diketahui'
                        )
                )
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($statusColor),

            Stat::make(
                'Semester Berjalan',
                $semesterMhs
                    ? "Semester {$semesterMhs}"
                    : '-'
            )
                ->description(
                    $activeTa?->nama_tahun
                        ?? 'Tidak ada Tahun Akademik aktif'
                )
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make(
                'IPK (Indeks Prestasi Kumulatif)',
                $ipk
            )
                ->description('Dari maksimal 4.00')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make(
                'Total SKS Ditempuh',
                "{$sksTotal} / {$sksLulusSyarat} SKS"
            )
                ->description(
                    "Progress kelulusan: {$progressPersen}%"
                )
                ->descriptionIcon('heroicon-m-document-check')
                ->color($progressColor),

            Stat::make(
                'Status KRS Semester Ini',
                $krsLabel
            )
                ->description(
                    $activeTa?->nama_tahun
                        ?? 'Tidak ada Tahun Akademik aktif'
                )
                ->descriptionIcon(
                    'heroicon-m-clipboard-document-list'
                )
                ->color($krsColor),

            Stat::make(
                'Status Keuangan Semester Ini',
                $keuanganLabel
            )
                ->description($keuanganDesc)
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($keuanganColor),
        ];
    }
}
