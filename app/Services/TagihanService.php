<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\GenerateTagihanJob;
use App\Models\GeneratorBatch;
use App\Models\KeuanganKomponenBiaya;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Services\Keuangan\BeasiswaDiskonService;
use App\Services\Keuangan\TargetMahasiswaResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TagihanService
{
    private const PREVIEW_DISPLAY_LIMIT = 100;

    /** @var array<int, \Illuminate\Support\Collection> cache detail tarif per skema_tarif_id, supaya mahasiswa 1 angkatan/prodi yang skemanya sama tidak query berulang */
    private array $cacheDetailSkema = [];

    public function __construct(
        private readonly TargetMahasiswaResolver $resolver,
        private readonly BeasiswaDiskonService $beasiswaService,
    ) {
    }

    /**
     * Memasukkan proses generate tagihan ke dalam antrean Laravel Queue.
     *
     * Sekarang membuat 1 row GeneratorBatch dulu SEBELUM dispatch, supaya
     * job punya batch_id untuk mencatat log per mahasiswa (generator_logs)
     * dan ringkasan akhir (generator_batches.summary_snapshot) - histori
     * generate jadi query-able lewat halaman Riwayat Generator, bukan
     * cuma teks di notifikasi Filament seperti sebelumnya.
     */
    public function generate(array $data): array
    {
        try {
            $userId = Auth::id();

            $batch = GeneratorBatch::create([
                'tahun_akademik_id' => $data['tahun_akademik_id'],
                'status' => 'PROCESSING',
                'parameter_snapshot' => $data,
                'started_at' => now(),
                'created_by' => $userId,
            ]);

            GenerateTagihanJob::dispatch($data, $userId, $batch->id);

            return [
                'status' => 'success',
                'mode' => 'queue',
                'batch_id' => $batch->id,
                'message' => 'Proses pembuatan tagihan telah dimasukkan ke antrean sistem. Silakan tunggu beberapa saat.',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Gagal memulai antrean sistem: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Preview SINKRON (read-only, tanpa queue) - dijalankan sebelum admin
     * menekan tombol Generate. Memakai TargetMahasiswaResolver yang sama
     * persis dengan yang dipakai GenerateTagihanJob, supaya jumlah &
     * kriteria yang ditampilkan preview dijamin identik dengan yang akan
     * benar-benar diproses job nanti.
     *
     * Angka AGREGAT (total_mahasiswa_kriteria, sudah_punya_tagihan, dst)
     * dihitung untuk SELURUH populasi target - murah, cuma exists()/count().
     *
     * Nominal & rincian komponen HANYA dihitung untuk mahasiswa yang masuk
     * daftar sampel (dibatasi PREVIEW_DISPLAY_LIMIT) - sengaja dibatasi,
     * karena menghitung diskon beasiswa per komponen itu query per
     * mahasiswa (sama seperti yang dilakukan job sungguhan). Kalau dipaksa
     * untuk seluruh populasi yang bisa ribuan, preview jadi seberat proses
     * generate itu sendiri dan bisa timeout di request thread.
     */
    public function preview(array $data): array
    {
        $tahunAkademikId = $data['tahun_akademik_id'];
        $tahunAkademik = RefTahunAkademik::find($tahunAkademikId);

        $agregat = [
            'total_mahasiswa_kriteria' => 0,
            'sudah_punya_tagihan' => 0,
            'siap_digenerate' => 0,
            'akan_gagal_tanpa_skema' => 0,
        ];

        $sampelSiap = [];
        $sampelGagal = [];
        $this->cacheDetailSkema = [];

        // Mode spesifik sengaja TIDAK difilter status aktif oleh
        // TargetMahasiswaResolver (lihat komentar di resolver) - dipakai
        // justru untuk kasus pengecualian yang admin pilih manual. Tapi
        // preview tetap perlu MEMBERI TAHU kondisi ini, bukan diam-diam,
        // supaya admin sadar sebelum klik Generate - makanya dicek di sini,
        // khusus untuk mode ini saja.
        $isSpesifik = ($data['tipe_target'] ?? null) === 'spesifik';

        $query = $this->resolver->resolve($data);

        $query->chunkById(200, function ($chunk) use (
            $tahunAkademikId, $tahunAkademik, $isSpesifik, &$agregat, &$sampelSiap, &$sampelGagal
        ) {
            /** @var Mahasiswa $mhs */
            foreach ($chunk as $mhs) {
                $agregat['total_mahasiswa_kriteria']++;

                $sudahAda = DB::table('tagihan_mahasiswas')
                    ->where('mahasiswa_id', $mhs->id)
                    ->where('tahun_akademik_id', $tahunAkademikId)
                    ->exists();

                if ($sudahAda) {
                    $agregat['sudah_punya_tagihan']++;
                    continue;
                }

                if (empty($mhs->program_id)) {
                    $agregat['akan_gagal_tanpa_skema']++;
                    if (count($sampelGagal) < self::PREVIEW_DISPLAY_LIMIT) {
                        $sampelGagal[] = [
                            'nim' => $mhs->nim,
                            'nama' => $mhs->person?->nama_lengkap,
                            'alasan' => 'Mahasiswa belum memiliki Program (program_id kosong).',
                        ];
                    }
                    continue;
                }

                $skemaTarif = DB::table('keuangan_skema_tarif')
                    ->where('angkatan_id', $mhs->angkatan_id)
                    ->where('prodi_id', $mhs->prodi_id)
                    ->where('program_kelas_id', $mhs->program_id)
                    ->where('is_active', 1)
                    ->first();

                if ($skemaTarif === null) {
                    $agregat['akan_gagal_tanpa_skema']++;
                    if (count($sampelGagal) < self::PREVIEW_DISPLAY_LIMIT) {
                        $sampelGagal[] = [
                            'nim' => $mhs->nim,
                            'nama' => $mhs->person?->nama_lengkap,
                            'alasan' => 'Skema tarif untuk Prodi/Angkatan/Program ini belum dikonfigurasi.',
                        ];
                    }
                    continue;
                }

                $agregat['siap_digenerate']++;

                if (count($sampelSiap) < self::PREVIEW_DISPLAY_LIMIT) {
                    $item = $this->hitungRincianTagihan($mhs, $skemaTarif, $tahunAkademik);

                    if ($isSpesifik) {
                        $item['status_warning'] = $this->cekStatusNonAktif($mhs->id, $tahunAkademikId);
                    }

                    $sampelSiap[] = $item;
                }
            }
        }, column: 'id');

        return [
            'agregat' => $agregat,
            'sampel_siap' => $sampelSiap,
            'sampel_gagal' => $sampelGagal,
            'dibatasi' => $agregat['siap_digenerate'] > self::PREVIEW_DISPLAY_LIMIT
                || $agregat['akan_gagal_tanpa_skema'] > self::PREVIEW_DISPLAY_LIMIT,
        ];
    }

    /**
     * Cuma dipanggil untuk mode 'spesifik' (lihat catatan di preview()).
     * Mengembalikan pesan warning kalau mahasiswa ini punya row status
     * eksplisit selain 'A' di riwayat_status_mahasiswas untuk tahun
     * akademik yang dipilih - null kalau statusnya aktif ATAU belum ada
     * row riwayat sama sekali (konsisten dengan definisi "aktif" yang
     * dipakai TargetMahasiswaResolver untuk mode kolektif).
     */
    private function cekStatusNonAktif(string $mahasiswaId, $tahunAkademikId): ?string
    {
        $status = DB::table('riwayat_status_mahasiswas')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('tahun_akademik_id', $tahunAkademikId)
            ->where('status_kuliah', '!=', 'A')
            ->value('status_kuliah');

        if ($status === null) {
            return null;
        }

        return "Status kuliah tercatat: {$status} (bukan Aktif) - mode Kolektif akan otomatis melewati mahasiswa ini, tapi mode Spesifik tetap memprosesnya karena dipilih manual.";
    }

    /**
     * Menghitung rincian komponen + total tagihan (bersih setelah diskon
     * beasiswa) untuk satu mahasiswa - kalkulasinya sama persis dengan
     * yang dilakukan GenerateTagihanJob::handle(), supaya angka yang
     * ditampilkan di preview cocok dengan tagihan yang benar-benar akan
     * terbit (bukan cuma nominal kotor dari skema tarif).
     */
    private function hitungRincianTagihan(Mahasiswa $mhs, object $skemaTarif, ?RefTahunAkademik $tahunAkademik): array
    {
        if (! isset($this->cacheDetailSkema[$skemaTarif->id])) {
            $this->cacheDetailSkema[$skemaTarif->id] = DB::table('keuangan_detail_tarif')
                ->join('keuangan_komponen_biaya', 'keuangan_komponen_biaya.id', '=', 'keuangan_detail_tarif.komponen_biaya_id')
                ->where('keuangan_detail_tarif.skema_tarif_id', $skemaTarif->id)
                ->select('keuangan_detail_tarif.*', 'keuangan_komponen_biaya.nama_komponen')
                ->get();
        }

        $detailTarif = $this->cacheDetailSkema[$skemaTarif->id];

        $komponenIds = $detailTarif->pluck('komponen_biaya_id')->toArray();
        $modelKomponens = KeuanganKomponenBiaya::whereIn('id', $komponenIds)->get()->keyBy('id');

        $rincianKomponen = [];
        $totalKotor = 0.0;
        $totalDiskon = 0.0;

        foreach ($detailTarif as $tarif) {
            $nominalDasar = (float) $tarif->nominal;
            $komponenModel = $modelKomponens->get($tarif->komponen_biaya_id);
            $nominalDiskon = 0.0;

            if ($komponenModel && $tahunAkademik) {
                $nominalDiskon = $this->beasiswaService->hitungDiskonUntukKomponen(
                    mahasiswa: $mhs,
                    komponen: $komponenModel,
                    tahunAkademik: $tahunAkademik,
                    nominalDasar: $nominalDasar,
                );
            }

            $rincianKomponen[] = [
                'nama_komponen' => $tarif->nama_komponen,
                'nominal_dasar' => $nominalDasar,
                'nominal_diskon' => $nominalDiskon,
                'nominal_bersih' => max(0, $nominalDasar - $nominalDiskon),
            ];

            $totalKotor += $nominalDasar;
            $totalDiskon += $nominalDiskon;
        }

        $totalBersih = max(0, $totalKotor - $totalDiskon);

        return [
            'nim' => $mhs->nim,
            'nama' => $mhs->person?->nama_lengkap,
            'total_kotor' => $totalKotor,
            'total_diskon' => $totalDiskon,
            'total_tagihan' => $totalBersih,
            'jumlah_komponen' => count($rincianKomponen),
            'rincian_komponen' => $rincianKomponen,
        ];
    }
}