<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\GeneratorBatch;
use App\Models\KeuanganKomponenBiaya;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Models\User;
use App\Services\Keuangan\BeasiswaDiskonService;
use App\Services\Keuangan\LedgerService;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateTagihanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Batas waktu eksekusi job (detik). Batch besar (ribuan mahasiswa)
     * butuh lebih dari default 60 detik queue worker.
     */
    public int $timeout = 1800; // 30 menit

    /**
     * Jangan retry otomatis kalau job gagal total (exception di luar loop).
     * Setiap mahasiswa sudah punya transaksi sendiri-sendiri di dalam loop,
     * jadi retry job penuh berisiko generate ulang ke mahasiswa yang sudah
     * berhasil di percobaan sebelumnya.
     */
    public int $tries = 1;

    /**
     * Ukuran chunk saat membaca data mahasiswa, supaya tidak load
     * ribuan row ke memori sekaligus.
     */
    protected int $chunkSize = 200;

    protected array $data;
    protected ?string $userId;

    /**
     * Nullable & default null supaya backward-compatible kalau ada kode
     * lain yang masih dispatch job ini tanpa batch id (mis. dari tinker/
     * command lama) - job tetap jalan normal, cuma tidak menulis log batch.
     */
    protected ?int $batchId;

    public function __construct(array $data, ?string $userId, ?int $batchId = null)
    {
        $this->data = $data;
        $this->userId = $userId;
        $this->batchId = $batchId;
    }

    public function handle(LedgerService $ledger): void
    {
        $batch = $this->batchId ? GeneratorBatch::find($this->batchId) : null;

        $tahunAkademikId = $this->data['tahun_akademik_id'];
        $tahunAkademik = RefTahunAkademik::find($tahunAkademikId);
        $namaTahun = $tahunAkademik ? $tahunAkademik->nama_tahun : 'Semester Berjalan';

        // Variabel perekam status proses
        $successCount = 0;
        $failedCount = 0;
        $skippedCount = 0;
        $errorLog = []; // Menampung pesan error/kendala spesifik per mahasiswa

        $query = Mahasiswa::query()->with('person');

        if ($this->data['tipe_target'] === 'kolektif') {
            if (!empty($this->data['prodi_id'])) {
                $query->where('prodi_id', $this->data['prodi_id']);
            }
            if (!empty($this->data['angkatan_id'])) {
                $query->where('angkatan_id', $this->data['angkatan_id']);
            }

            // Filter status aktif: skip mahasiswa yang punya status eksplisit
            // (cuti/lulus/DO/dsb) selain 'A' di semester ini. Mahasiswa yang
            // BELUM punya row riwayat_status_mahasiswas untuk semester ini
            // tetap diikutkan, karena tabel itu diisi manual oleh admin dan
            // mahasiswa aktif/baru bisa saja belum sempat diinput statusnya.
            $tahunAkademikIdFilter = $this->data['tahun_akademik_id'];
            $query->whereNotIn('id', function ($sub) use ($tahunAkademikIdFilter) {
                $sub->select('mahasiswa_id')
                    ->from('riwayat_status_mahasiswas')
                    ->where('tahun_akademik_id', $tahunAkademikIdFilter)
                    ->where('status_kuliah', '!=', 'A');
            });
        } else {
            // Target spesifik: pilihan manual admin, tidak difilter status aktif.
            $query->where('id', $this->data['mahasiswa_id']);
        }

        //  Instansiasi Service Beasiswa di luar loop agar memory efisien
        $beasiswaService = app(BeasiswaDiskonService::class);

        // lazy() membaca data per-chunk dari DB (cursor-based), bukan
        // load semua row ke memori sekaligus seperti get().
        $query->orderBy('id')->lazy($this->chunkSize)->each(function (Mahasiswa $mhs) use (
            $tahunAkademikId,
            $tahunAkademik,
            $namaTahun,
            $beasiswaService,
            $ledger,
            $batch,
            &$successCount,
            &$failedCount,
            &$skippedCount,
            &$errorLog,
        ) {
            DB::beginTransaction();
            try {
                // 1. Cek duplikasi tagihan
                $tagihanExists = DB::table('tagihan_mahasiswas')
                    ->where('mahasiswa_id', $mhs->id)
                    ->where('tahun_akademik_id', $tahunAkademikId)
                    ->exists();

                if ($tagihanExists) {
                    $skippedCount++;
                    $this->logMahasiswa($batch, $mhs->id, 'DILEWATI', null, 'Mahasiswa sudah memiliki tagihan di semester ini.');
                    DB::rollBack();
                    return;
                }

                // 2. Validasi program_id mahasiswa sebelum lookup skema tarif.
                //    (Kolom `program_kelas_id` TIDAK ADA di tabel mahasiswas —
                //    yang benar adalah `program_id`, FK ke ref_program.)
                if (empty($mhs->program_id)) {
                    throw new \Exception("Mahasiswa belum memiliki Program (program_id kosong), tidak bisa dicarikan skema tarif.");
                }

                // 3. Ambil skema tarif
                $skemaTarif = DB::table('keuangan_skema_tarif')
                    ->where('angkatan_id', $mhs->angkatan_id)
                    ->where('prodi_id', $mhs->prodi_id)
                    ->where('program_kelas_id', $mhs->program_id)
                    ->where('is_active', 1)
                    ->first();

                if (!$skemaTarif) {
                    throw new \Exception("Skema tarif untuk Prodi/Angkatan/Program mahasiswa ini belum dikonfigurasi.");
                }

                // 4. Ambil komponen tarif
                $detailTarif = DB::table('keuangan_detail_tarif')
                    ->join('keuangan_komponen_biaya', 'keuangan_komponen_biaya.id', '=', 'keuangan_detail_tarif.komponen_biaya_id')
                    ->where('keuangan_detail_tarif.skema_tarif_id', $skemaTarif->id)
                    ->select('keuangan_detail_tarif.*', 'keuangan_komponen_biaya.nama_komponen')
                    ->get();

                if ($detailTarif->isEmpty()) {
                    throw new \Exception("Komponen tarif pada skema biaya prodi ini masih kosong.");
                }

                // Pre-load Model KeuanganKomponenBiaya untuk parameter Service (mencegah N+1)
                $komponenIds = $detailTarif->pluck('komponen_biaya_id')->toArray();
                $modelKomponens = KeuanganKomponenBiaya::whereIn('id', $komponenIds)->get()->keyBy('id');

                $tagihanId = Str::uuid()->toString();
                $kodeTransaksi = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));

                $totalTagihanKotor = 0;
                $totalDiskonKeseluruhan = 0;
                $detailTagihanToInsert = [];

                foreach ($detailTarif as $tarif) {
                    $nominalDasar = (float) $tarif->nominal;

                    // Kalkulasi Diskon via Service Layer
                    $komponenModel = $modelKomponens->get($tarif->komponen_biaya_id);
                    $nominalDiskonKomponen = 0.0;

                    if ($komponenModel && $tahunAkademik) {
                        $nominalDiskonKomponen = $beasiswaService->hitungDiskonUntukKomponen(
                            mahasiswa: $mhs,
                            komponen: $komponenModel,
                            tahunAkademik: $tahunAkademik,
                            nominalDasar: $nominalDasar
                        );
                    }

                    $detailTagihanToInsert[] = [
                        'tagihan_id'             => $tagihanId,
                        'komponen_biaya_id'      => $tarif->komponen_biaya_id,
                        'nama_komponen_snapshot' => $tarif->nama_komponen,
                        'nominal_dasar'          => $nominalDasar,
                        'nominal_diskon'         => $nominalDiskonKomponen,
                        'nominal_terbayar'       => 0.00,
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ];

                    $totalTagihanKotor += $nominalDasar;
                    $totalDiskonKeseluruhan += $nominalDiskonKomponen;
                }

                $totalTagihanBersih = $totalTagihanKotor - $totalDiskonKeseluruhan;
                if ($totalTagihanBersih < 0) {
                    $totalTagihanBersih = 0;
                }

                // Insert Header
                DB::table('tagihan_mahasiswas')->insert([
                    'id'                => $tagihanId,
                    'mahasiswa_id'      => $mhs->id,
                    'tahun_akademik_id' => $tahunAkademikId,
                    'kode_transaksi'    => $kodeTransaksi,
                    'deskripsi'         => "Tagihan Biaya Kuliah Mahasiswa NIM {$mhs->nim} - {$namaTahun}",
                    'total_tagihan'     => $totalTagihanBersih,
                    'total_bayar'       => 0.00,
                    'status_bayar'      => 'BELUM',
                    'created_by'        => $this->userId,
                    'tenggat_waktu'     => now()->addDays(30),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);

                // Insert Detail
                if (!empty($detailTagihanToInsert)) {
                    DB::table('tagihan_mahasiswas_details')->insert($detailTagihanToInsert);
                }
                // Catat ke buku besar SEBELUM commit, supaya tagihan dan
                // entri ledger-nya atomic (satu-satunya kegagalan yang bisa
                // terjadi di sini akan ikut kena DB::rollBack() di bawah,
                // bukan meninggalkan tagihan tanpa jejak ledger).
                //
                // TIDAK memanggil recordBeasiswa() di sini — diskon
                // beasiswa sudah baked-in ke $totalTagihanBersih lewat
                // nominal_diskon di atas (BeasiswaDiskonService), jadi
                // mencatatnya lagi sebagai entri ADJUSTMENT terpisah akan
                // dobel hitung.
                $ledger->recordTagihan(
                    mahasiswaId: $mhs->id,
                    nominal: number_format($totalTagihanBersih, 2, '.', ''),
                    referensiDokumen: "tagihan-mahasiswa:{$tagihanId}",
                    keterangan: "Tagihan Biaya Kuliah Mahasiswa NIM {$mhs->nim} - {$namaTahun}",
                );
                DB::commit();
                $successCount++;
                $this->logMahasiswa($batch, $mhs->id, 'BERHASIL', $totalTagihanBersih, "Tagihan {$kodeTransaksi} terbit dengan " . count($detailTagihanToInsert) . ' komponen.');
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();

                // SQLSTATE 23000 / error code 1062 = duplicate entry.
                // Ini kena unique constraint tagihan_mahasiswas_mhs_tahun_akademik_unique,
                // artinya race condition: proses lain berhasil insert duluan di antara
                // exists()-check dan insert() job ini. Bukan kegagalan sungguhan,
                // hasil akhirnya tetap benar (mahasiswa punya 1 tagihan), jadi
                // dihitung sebagai "sudah ada", bukan "gagal".
                if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), 'tagihan_mahasiswas_mhs_tahun_akademik_unique')) {
                    $skippedCount++;
                    $this->logMahasiswa($batch, $mhs->id, 'DILEWATI', null, 'Race condition terdeteksi (tagihan sudah dibuat proses lain), dilewati dengan aman.');
                } else {
                    $failedCount++;
                    $namaMhs = $mhs->person->nama_lengkap ?? 'Unknown';
                    $pesan = "NIM {$mhs->nim} ({$namaMhs}): " . $e->getMessage();
                    $errorLog[] = $pesan;
                    $this->logMahasiswa($batch, $mhs->id, 'GAGAL', null, $e->getMessage());
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $failedCount++;
                $namaMhs = $mhs->person->nama_lengkap ?? 'Unknown';
                $pesan = "NIM {$mhs->nim} ({$namaMhs}): " . $e->getMessage();
                $errorLog[] = $pesan;
                $this->logMahasiswa($batch, $mhs->id, 'GAGAL', null, $e->getMessage());
            }
        });

        if ($batch) {
            $batch->update([
                'status' => 'COMPLETED',
                'completed_at' => now(),
                'total_mahasiswa' => $successCount + $failedCount + $skippedCount,
                'total_berhasil' => $successCount,
                'total_gagal' => $failedCount,
                'total_skip' => $skippedCount,
                'summary_snapshot' => [
                    'total_berhasil' => $successCount,
                    'total_gagal' => $failedCount,
                    'total_skip' => $skippedCount,
                ],
            ]);
        }

        // === KIRIM NOTIFIKASI DENGAN DETAIL REKAPAN ===
        if ($this->userId) {
            $admin = User::find($this->userId);
            if ($admin) {
                $bodySummary = "Hasil proses: {$successCount} Berhasil, {$failedCount} Gagal, {$skippedCount} Sudah Ada.";

                if (!empty($errorLog)) {
                    $bodySummary .= "\n\nBeberapa Kendala:\n" . implode("\n", array_slice($errorLog, 0, 5));
                    if (count($errorLog) > 5) {
                        $bodySummary .= "\n...dan " . (count($errorLog) - 5) . " kendala lainnya. Lihat detail lengkap di Riwayat Generator.";
                    }
                }

                Notification::make()
                    ->title('Laporan Generate Tagihan Selesai')
                    ->body($bodySummary)
                    ->status($failedCount > 0 ? 'warning' : 'success')
                    ->sendToDatabase($admin);
            }
        }
    }

    /**
     * Dipanggil otomatis kalau job gagal total (exception tidak tertangkap,
     * atau timeout). Karena $tries = 1, ini adalah kesempatan terakhir untuk
     * memberitahu admin bahwa proses generate tidak selesai dengan baik.
     */
    public function failed(\Throwable $exception): void
    {
        if ($this->batchId) {
            GeneratorBatch::where('id', $this->batchId)->update([
                'status' => 'FAILED',
                'completed_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);
        }

        if (!$this->userId) {
            return;
        }

        $admin = User::find($this->userId);
        if (!$admin) {
            return;
        }

        Notification::make()
            ->title('Generate Tagihan Gagal Total')
            ->body('Proses generate tagihan berhenti karena error sistem: ' . $exception->getMessage() . '. Silakan cek data yang sudah masuk sebelum menjalankan ulang, untuk menghindari duplikasi.')
            ->status('danger')
            ->sendToDatabase($admin);
    }

    /**
     * Insert 1 baris log per mahasiswa - dipanggil di SETIAP titik keluar
     * dari try/catch di atas (berhasil/gagal/dilewati), persis pola yang
     * sama dengan SinkronisasiTagihanJob::logMahasiswa(). Kalau job ini
     * di-dispatch tanpa batch (mis. dari kode lama yang belum di-update),
     * $batch bernilai null dan logging ini otomatis dilewati - job tetap
     * berjalan normal.
     */
    private function logMahasiswa(?GeneratorBatch $batch, string $mahasiswaId, string $status, ?float $totalTagihan, ?string $pesan): void
    {
        if (! $batch) {
            return;
        }

        DB::table('generator_logs')->insert([
            'generator_batch_id' => $batch->id,
            'mahasiswa_id' => $mahasiswaId,
            'status' => $status,
            'total_tagihan' => $totalTagihan,
            'pesan' => $pesan,
            'created_at' => now(),
        ]);
    }
}