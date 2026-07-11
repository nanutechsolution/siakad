<?php

declare(strict_types=1);

namespace App\Services\Akademik;

use App\DTOs\KrsValidationResult;
use App\Enums\StatusKuliah;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Models\JadwalKuliah;
use Illuminate\Support\Facades\DB;

class KrsValidationService
{
    /**
     * Gate 1: Periode KRS
     */
    public function checkPeriode(RefTahunAkademik $ta, bool $isOverride = false): KrsValidationResult
    {
        if ($isOverride) {
            return KrsValidationResult::pass('GATE_PERIODE', 'Override manual periode KRS aktif.');
        }

        if (!$ta->buka_krs) {
            return KrsValidationResult::fail('GATE_PERIODE', 'Periode pengisian KRS belum dibuka atau sudah ditutup.');
        }

        if ($ta->is_locked_krs) {
            return KrsValidationResult::fail('GATE_PERIODE', 'Periode KRS saat ini sedang dikunci oleh sistem.');
        }

        return KrsValidationResult::pass('GATE_PERIODE');
    }

    /**
     * Gate 2: Status & Kontinuitas Mahasiswa (Gap Semester)
     */
    public function checkStatusMahasiswa(Mahasiswa $mahasiswa, RefTahunAkademik $taTarget): KrsValidationResult
    {
        // Cari semester aktif sebelumnya (Ganjil/Genap), abaikan semester pendek (3)
        $previousTa = RefTahunAkademik::whereIn('semester', [1, 2])
            ->where('tanggal_mulai', '<', $taTarget->tanggal_mulai)
            ->orderBy('tanggal_mulai', 'desc')
            ->first();

        $needsDispensasi = false;
        $reason = '';

        if ($previousTa) {
            $riwayatSebelumnya = DB::table('riwayat_status_mahasiswas')
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('tahun_akademik_id', $previousTa->id)
                ->first();

            if (!$riwayatSebelumnya) {
                $needsDispensasi = true;
                $reason = "Terdeteksi gap semester (tidak ada riwayat pada semester {$previousTa->nama_tahun}).";
            } elseif ($riwayatSebelumnya->status_kuliah !== StatusKuliah::AKTIF->value) {
                $needsDispensasi = true;
                $reason = "Status mahasiswa pada semester {$previousTa->nama_tahun} bukan AKTIF.";
            }
        }

        if ($needsDispensasi) {
            // Cek dispensasi jenis KRS yang mencakup tanggal mulai s/d tanggal selesai KRS di TA Target
            $hasDispensasi = DB::table('dispensasi_akademiks')
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('jenis', 'KRS')
                ->where('status', 'AKTIF')
                ->where('berlaku_mulai', '<=', $taTarget->tgl_selesai_krs)
                ->where('berlaku_sampai', '>=', $taTarget->tgl_mulai_krs)
                ->exists();

            if (!$hasDispensasi) {
                return KrsValidationResult::fail('GATE_KONTINUITAS', $reason . ' Wajib memiliki dispensasi KRS yang masih berlaku untuk periode ini.');
            }
        }

        return KrsValidationResult::pass('GATE_KONTINUITAS');
    }

    /**
     * Gate 3: Keuangan
     */
    public function checkKeuangan(Mahasiswa $mahasiswa, RefTahunAkademik $ta, bool $isOverride = false): KrsValidationResult
    {
        if ($isOverride) {
            return KrsValidationResult::pass('GATE_KEUANGAN', 'Override manual validasi keuangan aktif.');
        }

        // 1. Cek Tunggakan dari Semester-Semester Sebelumnya
        // Menjumlahkan sisa tagihan di mana tahun akademik BUKAN tahun akademik yang sedang diproses
        $tunggakanLalu = DB::table('tagihan_mahasiswas')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', '!=', $ta->id)
            ->where('status_bayar', '!=', 'LUNAS')
            ->whereNull('deleted_at')
            ->sum(DB::raw('total_tagihan - total_bayar'));

        if ($tunggakanLalu > 0) {
            return KrsValidationResult::fail(
                'GATE_KEUANGAN',
                "Terblokir: Mahasiswa memiliki tunggakan pembayaran dari semester sebelumnya sebesar Rp " . number_format((float)$tunggakanLalu, 0, ',', '.') . ". Wajib dilunasi sebelum mengisi KRS."
            );
        }

        // 2. Cek Kebijakan Pembayaran Semester Berjalan
        $policy = DB::table('payment_policies')
            ->where('tahun_akademik_id', $ta->id)
            ->where('aktif', 1)
            ->where(function ($query) use ($mahasiswa) {
                $query->where('prodi_id', $mahasiswa->prodi_id)
                    ->orWhereNull('prodi_id');
            })
            ->where(function ($query) use ($mahasiswa) {
                $query->where('program_kelas_id', $mahasiswa->program_id)
                    ->orWhereNull('program_kelas_id');
            })
            // Prioritaskan kebijakan yang paling spesifik (bukan NULL) agar berada di urutan teratas
            ->orderByRaw('prodi_id IS NULL, program_kelas_id IS NULL')
            ->first();

        if (!$policy) {
            return KrsValidationResult::fail('GATE_KEUANGAN', 'Kebijakan pembayaran (Payment Policy) untuk prodi dan program ini belum dikonfigurasi. Hubungi bagian Keuangan.');
        }

        // 3. Cek Apakah Tagihan Semester Berjalan SUDAH Diterbitkan
        $tagihanSemesterIni = DB::table('tagihan_mahasiswas')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $ta->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$tagihanSemesterIni) {
            return KrsValidationResult::fail('GATE_KEUANGAN', 'Terblokir: Tagihan untuk semester berjalan belum diterbitkan oleh bagian Keuangan.');
        }

        // --- PERBAIKAN UTAMA DI SINI ---
        // Jika tagihan utama semester ini secara keseluruhan sudah berstatus LUNAS,
        // bypass pengecekan per komponen biaya dan langsung nyatakan lolos validasi.
        if (isset($tagihanSemesterIni->status_bayar) && strtoupper($tagihanSemesterIni->status_bayar) === 'LUNAS') {
            return KrsValidationResult::pass('GATE_KEUANGAN');
        }
        // ------------------------------

        // 4. Evaluasi Rincian Pembayaran sesuai Payment Policy (Hanya dijalankan jika BELUM LUNAS)
        $policyDetails = DB::table('payment_policy_details')
            ->where('payment_policy_id', $policy->id)
            ->where('wajib', 1)
            ->get();

        foreach ($policyDetails as $detail) {
            $realisasi = DB::table('tagihan_mahasiswas_details')
                ->where('tagihan_id', $tagihanSemesterIni->id)
                ->where('komponen_biaya_id', $detail->komponen_biaya_id)
                ->select('nominal_tagihan', 'nominal_terbayar')
                ->first();

            // Jika kebijakan mengatakan wajib, tapi di tagihan mahasiswa tidak ada rincian komponen tersebut
            if (!$realisasi) {
                return KrsValidationResult::fail('GATE_KEUANGAN', 'Rincian komponen biaya wajib belum dimasukkan ke dalam tagihan mahasiswa ini.');
            }

            $nominalTerbayar = (float) $realisasi->nominal_terbayar;
            $nominalTagihan = (float) $realisasi->nominal_tagihan;

            $minimumSesuaiPersen = $nominalTagihan * ((float) $detail->minimal_persen / 100);
            $minimumNominal = (float) $detail->minimal_nominal;

            $targetBayar = $minimumNominal > 0 ? $minimumNominal : $minimumSesuaiPersen;

            if ($nominalTerbayar < $targetBayar) {
                return KrsValidationResult::fail(
                    'GATE_KEUANGAN',
                    "Syarat pembayaran komponen belum terpenuhi. Minimal harus dibayar: Rp " . number_format($targetBayar, 0, ',', '.') . " | Telah dibayar: Rp " . number_format($nominalTerbayar, 0, ',', '.')
                );
            }
        }

        return KrsValidationResult::pass('GATE_KEUANGAN');
    }

    /**
     * Gate 4: SKS Maksimal
     */
    public function checkSksMaksimal(Mahasiswa $mahasiswa, int $requestedSks, bool $hasDispensasi = false): KrsValidationResult
    {
        if ($hasDispensasi) {
            return KrsValidationResult::pass('GATE_SKS', 'Validasi SKS dilewati karena terdapat dispensasi aktif.');
        }

        $latestRiwayat = DB::table('riwayat_status_mahasiswas')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('tahun_akademik_id', 'desc')
            ->first();

        $maxSks = 0;

        if ($latestRiwayat) {
            $ips = (float) $latestRiwayat->ips;
            $aturan = DB::table('ref_aturan_sks')
                ->where('min_ips', '<=', $ips)
                ->where('max_ips', '>=', $ips)
                ->first();

            $maxSks = $aturan ? (int) $aturan->max_sks : 24;
        } else {
            // Mahasiswa Baru
            $sksSemester1 = DB::table('kurikulum_mata_kuliah')
                ->where('kurikulum_id', $mahasiswa->kurikulum_id)
                ->where('semester_paket', 1)
                ->selectRaw('SUM(sks_tatap_muka + sks_praktek + sks_lapangan) as total_sks')
                ->value('total_sks');

            if (!$sksSemester1 || (int) $sksSemester1 === 0) {
                return KrsValidationResult::fail('GATE_SKS', 'Kurikulum semester 1 belum terkonfigurasi. Tidak dapat menentukan batas SKS mahasiswa baru.');
            }
            $maxSks = (int) $sksSemester1;
        }

        if ($requestedSks > $maxSks) {
            return KrsValidationResult::fail('GATE_SKS', "Total SKS yang diambil ({$requestedSks}) melebihi batas maksimal yang diizinkan ({$maxSks} SKS).");
        }

        return KrsValidationResult::pass('GATE_SKS');
    }

    /**
     * Gate 5: Prasyarat Mata Kuliah
     */
    public function checkPrasyarat(Mahasiswa $mahasiswa, array $jadwalKuliahIds): KrsValidationResult
    {
        $mataKuliahIds = DB::table('jadwal_kuliah')
            ->whereIn('id', $jadwalKuliahIds)
            ->pluck('mata_kuliah_id')
            ->toArray();

        foreach ($mataKuliahIds as $mkId) {
            $kurikulumMk = DB::table('kurikulum_mata_kuliah')
                ->where('kurikulum_id', $mahasiswa->kurikulum_id)
                ->where('mata_kuliah_id', $mkId)
                ->first();

            if (!$kurikulumMk) continue;

            $prasyaratList = DB::table('kurikulum_mk_prasyarat')
                ->where('kurikulum_mk_id', $kurikulumMk->id)
                ->get();

            if ($prasyaratList->isEmpty()) continue;

            $andPassed = true;
            $orPassed = false;
            $hasOr = false;

            foreach ($prasyaratList as $syarat) {
                $prasyaratMkId = DB::table('kurikulum_mata_kuliah')
                    ->where('id', $syarat->prasyarat_kurikulum_mk_id)
                    ->value('mata_kuliah_id');

                $transkrip = DB::table('akademik_transkrip')
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->where('mata_kuliah_id', $prasyaratMkId)
                    ->first();

                $nilaiIndeks = $transkrip ? (float) $transkrip->nilai_indeks_final : 0.0;
                $isPassed = $nilaiIndeks >= (float) $syarat->min_nilai;

                if ($syarat->logic_type === 'AND') {
                    if (!$isPassed) $andPassed = false;
                } elseif ($syarat->logic_type === 'OR') {
                    $hasOr = true;
                    if ($isPassed) $orPassed = true;
                }
            }

            if (!$andPassed || ($hasOr && !$orPassed)) {
                $namaMk = DB::table('master_mata_kuliahs')->where('id', $mkId)->value('nama_mk');
                return KrsValidationResult::fail('GATE_PRASYARAT', "Syarat nilai prasyarat untuk mata kuliah {$namaMk} belum terpenuhi.");
            }
        }

        return KrsValidationResult::pass('GATE_PRASYARAT');
    }

    /**
     * Gate 6: Duplikasi MK & Bentrok Jadwal
     */
    public function checkDuplikasiDanBentrok(array $jadwalKuliahIds): KrsValidationResult
    {
        if (empty($jadwalKuliahIds)) {
            return KrsValidationResult::pass('GATE_JADWAL');
        }

        $jadwals = DB::table('jadwal_kuliah')
            ->join('master_mata_kuliahs', 'master_mata_kuliahs.id', '=', 'jadwal_kuliah.mata_kuliah_id')
            ->whereIn('jadwal_kuliah.id', $jadwalKuliahIds)
            ->select('jadwal_kuliah.*', 'master_mata_kuliahs.nama_mk')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        // Cek duplikasi MK
        $mkTaken = [];
        foreach ($jadwals as $jadwal) {
            if (in_array($jadwal->mata_kuliah_id, $mkTaken)) {
                return KrsValidationResult::fail('GATE_JADWAL', "Duplikasi Mata Kuliah terdeteksi: {$jadwal->nama_mk} diambil lebih dari satu kali.");
            }
            $mkTaken[] = $jadwal->mata_kuliah_id;
        }

        // Cek Bentrok
        $groupedByHari = $jadwals->groupBy('hari');
        foreach ($groupedByHari as $hari => $jadwalsPerHari) {
            if (empty($hari)) continue;

            $lastJadwal = null;
            foreach ($jadwalsPerHari as $jadwal) {
                if ($lastJadwal && $jadwal->jam_mulai && $lastJadwal->jam_selesai) {
                    if ($jadwal->jam_mulai < $lastJadwal->jam_selesai) {
                        return KrsValidationResult::fail('GATE_JADWAL', "Terjadi bentrok jadwal pada hari {$hari} antara {$lastJadwal->nama_mk} dan {$jadwal->nama_mk}.");
                    }
                }
                $lastJadwal = $jadwal;
            }
        }

        return KrsValidationResult::pass('GATE_JADWAL');
    }

    /**
     * Gate 7: Kuota Kelas
     */
    public function checkKuotaKelas(array $jadwalKuliahIds): KrsValidationResult
    {
        if (empty($jadwalKuliahIds)) {
            return KrsValidationResult::pass('GATE_KUOTA');
        }

        $jadwals = DB::table('jadwal_kuliah')
            ->join('master_mata_kuliahs', 'master_mata_kuliahs.id', '=', 'jadwal_kuliah.mata_kuliah_id')
            ->whereIn('jadwal_kuliah.id', $jadwalKuliahIds)
            ->select('jadwal_kuliah.*', 'master_mata_kuliahs.nama_mk')
            ->get();

        foreach ($jadwals as $jadwal) {
            if ($jadwal->isi_kelas >= $jadwal->kuota_kelas) {
                return KrsValidationResult::fail('GATE_KUOTA', "Kapasitas kelas untuk mata kuliah {$jadwal->nama_mk} sudah penuh ({$jadwal->isi_kelas}/{$jadwal->kuota_kelas}).");
            }
        }

        return KrsValidationResult::pass('GATE_KUOTA');
    }
}