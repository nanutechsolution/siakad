<?php

namespace App\Filament\Mahasiswa\Widgets;

use App\Enums\StatusKuliah;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
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

        // Jembatan Auth: Cari Mahasiswa berdasarkan person_id user yang login
        $mahasiswa = Mahasiswa::with(['prodi', 'person', 'kurikulum'])
            ->where('person_id', $user->person_id)
            ->first();

        if (!$mahasiswa) {
            return [
                Stat::make('Status Akses', 'Akses Ditolak')
                    ->description('Akun Anda tidak terhubung dengan data Mahasiswa.')
                    ->color('danger'),
            ];
        }

        $activeTa = RefTahunAkademik::where('is_active', 1)->first();

        // Ambil riwayat semester terakhir untuk IPK dan SKS
        $riwayatTerakhir = DB::table('riwayat_status_mahasiswas')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('tahun_akademik_id', 'desc')
            ->first();

        $ipk = $riwayatTerakhir ? number_format((float) $riwayatTerakhir->ipk, 2, '.', '') : '0.00';
        $sksTotal = $riwayatTerakhir ? (int) $riwayatTerakhir->sks_total : 0;

        $rawStatus = $riwayatTerakhir ? $riwayatTerakhir->status_kuliah : StatusKuliah::AKTIF->value;
        $statusLabel = StatusKuliah::tryFrom($rawStatus)?->label() ?? 'Tidak Diketahui';

        $statusColor = match ($rawStatus) {
            StatusKuliah::AKTIF->value => 'success',
            StatusKuliah::CUTI->value => 'warning',
            default => 'danger',
        };

        // Progress SKS kelulusan: berbasis kurikulum mahasiswa masing-masing,
        // bukan angka tetap, karena tiap kurikulum bisa beda syarat lulus.
        $sksLulusSyarat = (int) ($mahasiswa->kurikulum?->jumlah_sks_lulus ?? 144);
        $progressPersen = $sksLulusSyarat > 0
            ? min(100, round(($sksTotal / $sksLulusSyarat) * 100, 1))
            : 0;
        $progressColor = match (true) {
            $progressPersen >= 100 => 'success',
            $progressPersen >= 75 => 'primary',
            $progressPersen >= 40 => 'warning',
            default => 'gray',
        };

        // Estimasi Semester Berjalan (formula sama dengan PengisianKrsPage)
        $semesterMhs = null;
        if ($activeTa && $activeTa->kode_tahun) {
            $tahunAngkatan = (int) $mahasiswa->angkatan_id;
            $tahunSekarang = (int) substr($activeTa->kode_tahun, 0, 4);
            $semesterMhs = (($tahunSekarang - $tahunAngkatan) * 2) + ($activeTa->semester == 1 ? 1 : 2);
            $semesterMhs = $semesterMhs > 0 ? $semesterMhs : 1;
        }

        // Status KRS semester berjalan
        $krsLabelMap = [
            'DRAFT'      => ['Draft (Belum Diajukan)', 'gray'],
            'DIAJUKAN'   => ['Menunggu Persetujuan Dosen Wali', 'warning'],
            'DISETUJUI'  => ['Disetujui', 'success'],
            'DITOLAK'    => ['Ditolak — Perlu Direvisi', 'danger'],
            'DIBATALKAN' => ['Dibatalkan', 'gray'],
        ];

        $statusKrsRaw = $activeTa
            ? DB::table('krs')
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('tahun_akademik_id', $activeTa->id)
                ->value('status_krs')
            : null;

        [$krsLabel, $krsColor] = $statusKrsRaw
            ? ($krsLabelMap[$statusKrsRaw] ?? [$statusKrsRaw, 'gray'])
            : ['Belum Mengisi KRS', 'danger'];

        // Status Keuangan semester berjalan
        $tagihanAktif = $activeTa
            ? DB::table('tagihan_mahasiswas')
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('tahun_akademik_id', $activeTa->id)
                ->whereNull('deleted_at')
                ->first()
            : null;

        if (!$tagihanAktif) {
            $keuanganLabel = 'Tagihan Belum Terbit';
            $keuanganColor = 'gray';
            $keuanganDesc = 'Hubungi bagian Keuangan jika sudah masuk periode KRS.';
        } else {
            $statusBayar = strtoupper($tagihanAktif->status_bayar ?? '');
            $sisaTagihan = (float) $tagihanAktif->total_tagihan - (float) $tagihanAktif->total_bayar;

            if ($statusBayar === 'LUNAS') {
                $keuanganLabel = 'Lunas';
                $keuanganColor = 'success';
                $keuanganDesc = 'Tidak ada tunggakan pada semester ini.';
            } else {
                $keuanganLabel = 'Belum Lunas';
                $keuanganColor = 'danger';
                $keuanganDesc = 'Sisa tagihan: Rp ' . number_format(max($sisaTagihan, 0), 0, ',', '.');
            }
        }

        return [
            Stat::make('Status Mahasiswa', $statusLabel)
                ->description($mahasiswa->nim . ' - ' . ($mahasiswa->prodi->nama_prodi ?? 'Prodi Tidak Diketahui'))
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($statusColor),

            Stat::make('Semester Berjalan', $semesterMhs ? "Semester {$semesterMhs}" : '-')
                ->description($activeTa->nama_tahun ?? 'Tidak ada Tahun Akademik aktif')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('IPK (Indeks Prestasi Kumulatif)', $ipk)
                ->description('Dari maksimal 4.00')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('Total SKS Ditempuh', $sksTotal . ' / ' . $sksLulusSyarat . ' SKS')
                ->description("Progress kelulusan: {$progressPersen}%")
                ->descriptionIcon('heroicon-m-document-check')
                ->color($progressColor),

            Stat::make('Status KRS Semester Ini', $krsLabel)
                ->description($activeTa ? $activeTa->nama_tahun : 'Tidak ada TA aktif')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color($krsColor),

            Stat::make('Status Keuangan Semester Ini', $keuanganLabel)
                ->description($keuanganDesc)
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($keuanganColor),
        ];
    }
}