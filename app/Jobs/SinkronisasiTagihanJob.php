<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\KeuanganKomponenBiaya;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Models\SinkronisasiBatch;
use App\Models\SinkronisasiReviewItem;
use App\Models\TagihanMahasiswaDetail;
use App\Models\User;
use App\Services\Keuangan\BeasiswaDiskonService;
use App\Services\Keuangan\KomponenTagihanComparator;
use App\Services\Keuangan\LedgerService;
use App\Services\Keuangan\TargetMahasiswaResolver;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SinkronisasiTagihanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    /**
     * Sama seperti GenerateTagihanJob: jangan retry otomatis. Setiap
     * mahasiswa punya transaksi sendiri, retry job penuh berisiko
     * memproses ulang mahasiswa yang sudah berhasil di percobaan
     * sebelumnya (walau sebagian besar operasi di sini sudah idempotent,
     * retry penuh tetap tidak diperlukan dan bisa membingungkan audit
     * trail batch).
     */
    public int $tries = 1;

    protected int $chunkSize = 200;

    public function __construct(
        protected string $batchId,
        protected array $data,
        protected ?string $userId,
        protected bool $dryRun = false,
    ) {
    }

    public function handle(
        LedgerService $ledger,
        TargetMahasiswaResolver $resolver,
        KomponenTagihanComparator $comparator,
    ): void {
        $batch = SinkronisasiBatch::findOrFail($this->batchId);
        $tahunAkademikId = $this->data['tahun_akademik_id'];
        $tahunAkademik = RefTahunAkademik::find($tahunAkademikId);
        $namaTahun = $tahunAkademik ? $tahunAkademik->nama_tahun : 'Semester Berjalan';

        $counter = [
            'total_mahasiswa' => 0,
            'total_ditambah' => 0,
            'total_review' => 0,
            'total_warning' => 0,
            'total_dilewati' => 0,
            'total_error' => 0,
        ];
        $warningKomponenAgregat = []; // nama_komponen => jumlah mahasiswa terdampak

        $beasiswaService = app(BeasiswaDiskonService::class);
        $query = $resolver->resolve($this->data);

        try {
            $query->lazy($this->chunkSize)->each(function (Mahasiswa $mhs) use (
                $tahunAkademikId, $tahunAkademik, $namaTahun, $beasiswaService,
                $ledger, $comparator, $batch, &$counter, &$warningKomponenAgregat,
            ) {
                $counter['total_mahasiswa']++;

                DB::beginTransaction();
                try {
                    $tagihan = DB::table('tagihan_mahasiswas')
                        ->where('mahasiswa_id', $mhs->id)
                        ->where('tahun_akademik_id', $tahunAkademikId)
                        ->lockForUpdate()
                        ->first();

                    if ($tagihan === null) {
                        // Mahasiswa belum punya tagihan sama sekali -> bukan
                        // domain Sinkronisasi (itu tugas Generator). Dilewati.
                        $counter['total_dilewati']++;
                        $this->logMahasiswa($batch->id, $mhs->id, 'DILEWATI', 0, 0, 0, 'Belum memiliki tagihan reguler di semester ini.');
                        DB::commit();
                        return;
                    }

                    if (empty($mhs->program_id)) {
                        throw new \Exception('Mahasiswa belum memiliki Program (program_id kosong).');
                    }

                    $skemaTarif = DB::table('keuangan_skema_tarif')
                        ->where('angkatan_id', $mhs->angkatan_id)
                        ->where('prodi_id', $mhs->prodi_id)
                        ->where('program_kelas_id', $mhs->program_id)
                        ->where('is_active', 1)
                        ->first();

                    if ($skemaTarif === null) {
                        $counter['total_dilewati']++;
                        $this->logMahasiswa($batch->id, $mhs->id, 'DILEWATI', 0, 0, 0, 'Skema tarif untuk Prodi/Angkatan/Program mahasiswa ini belum dikonfigurasi.');
                        DB::commit();
                        return;
                    }

                    $detailSkema = DB::table('keuangan_detail_tarif')
                        ->join('keuangan_komponen_biaya', 'keuangan_komponen_biaya.id', '=', 'keuangan_detail_tarif.komponen_biaya_id')
                        ->where('keuangan_detail_tarif.skema_tarif_id', $skemaTarif->id)
                        ->select('keuangan_detail_tarif.*', 'keuangan_komponen_biaya.nama_komponen')
                        ->get();

                    $detailExisting = TagihanMahasiswaDetail::where('tagihan_id', $tagihan->id)
                        ->lockForUpdate()
                        ->get();

                    $hasil = $comparator->bandingkan($detailSkema, $detailExisting);

                    foreach ($hasil->toWarn as $warn) {
                        $warningKomponenAgregat[$warn['nama_komponen_snapshot']] =
                            ($warningKomponenAgregat[$warn['nama_komponen_snapshot']] ?? 0) + 1;
                    }
                    $counter['total_warning'] += count($hasil->toWarn);

                    if ($this->dryRun) {
                        // Dry run: TIDAK ada write ke tagihan_mahasiswas_details,
                        // tagihan_mahasiswas, atau sinkronisasi_review_items.
                        // Hanya menghitung, supaya hasilnya bisa dipakai untuk
                        // audit/reproduksi tanpa efek samping apa pun.
                        $counter['total_ditambah'] += count($hasil->toAdd);
                        $counter['total_review'] += count($hasil->toReview);
                        $this->logMahasiswa(
                            $batch->id, $mhs->id, 'BERHASIL',
                            count($hasil->toAdd), count($hasil->toReview), count($hasil->toWarn),
                            'Dry run - tidak ada perubahan ditulis.'
                        );
                        DB::commit();
                        return;
                    }

                    $totalDitambahkan = $this->prosesToAdd(
                        $hasil->toAdd, $mhs, $tagihan, $tahunAkademik,
                        $beasiswaService, $ledger,
                    );
                    $counter['total_ditambah'] += count($hasil->toAdd);

                    $jumlahReviewBaru = $this->prosesToReview($hasil->toReview, $batch->id, $tagihan->id, $mhs->id);
                    $counter['total_review'] += count($hasil->toReview);

                    if ($totalDitambahkan > 0) {
                        DB::table('tagihan_mahasiswas')
                            ->where('id', $tagihan->id)
                            ->increment('total_tagihan', $totalDitambahkan);
                    }

                    $this->logMahasiswa(
                        $batch->id, $mhs->id, 'BERHASIL',
                        count($hasil->toAdd), count($hasil->toReview), count($hasil->toWarn),
                        count($hasil->toAdd) > 0
                            ? 'Menambahkan ' . count($hasil->toAdd) . ' komponen baru.'
                            : ($jumlahReviewBaru > 0 ? 'Ditemukan komponen berubah nominal, masuk daftar review.' : 'Tidak ada perubahan.')
                    );

                    DB::commit();
                } catch (\Illuminate\Database\QueryException $e) {
                    DB::rollBack();
                    // 23000 = duplicate entry, race condition dengan proses
                    // lain (batch sinkronisasi lain / generator) yang sudah
                    // insert komponen yang sama duluan. Hasil akhirnya tetap
                    // benar, jadi dihitung sebagai "tidak ada yang perlu
                    // ditambahkan lagi", bukan gagal.
                    if ((int) $e->getCode() === 23000) {
                        $counter['total_dilewati']++;
                        $this->logMahasiswa($batch->id, $mhs->id, 'DILEWATI', 0, 0, 0, 'Race condition terdeteksi (komponen sudah ditambahkan proses lain), dilewati dengan aman.');
                    } else {
                        $counter['total_error']++;
                        $this->logMahasiswa($batch->id, $mhs->id, 'GAGAL', 0, 0, 0, $e->getMessage());
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    $counter['total_error']++;
                    $this->logMahasiswa($batch->id, $mhs->id, 'GAGAL', 0, 0, 0, $e->getMessage());
                }
            });

            $batch->update([
                'status' => 'COMPLETED',
                'completed_at' => now(),
                'total_mahasiswa' => $counter['total_mahasiswa'],
                'total_ditambah' => $counter['total_ditambah'],
                'total_review' => $counter['total_review'],
                'total_warning' => $counter['total_warning'],
                'total_dilewati' => $counter['total_dilewati'],
                'total_error' => $counter['total_error'],
                'summary_snapshot' => [
                    ...$counter,
                    'mode' => $this->dryRun ? 'DRY_RUN' : 'EKSEKUSI',
                    'komponen_warning' => $warningKomponenAgregat,
                ],
            ]);
        } catch (\Throwable $e) {
            $batch->update([
                'status' => 'FAILED',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }

        $this->kirimNotifikasi($batch, $counter);
    }

    /**
     * @return string total nominal yang ditambahkan ke header (buat increment).
     */
    private function prosesToAdd(
        array $toAdd,
        Mahasiswa $mhs,
        object $tagihan,
        ?RefTahunAkademik $tahunAkademik,
        BeasiswaDiskonService $beasiswaService,
        LedgerService $ledger,
    ): string {
        if (empty($toAdd)) {
            return '0';
        }

        $komponenIds = array_column($toAdd, 'komponen_biaya_id');
        $modelKomponens = KeuanganKomponenBiaya::whereIn('id', $komponenIds)->get()->keyBy('id');

        $totalDitambahkan = 0.0;

        foreach ($toAdd as $item) {
            $nominalDasar = $item['nominal'];
            $komponenModel = $modelKomponens->get($item['komponen_biaya_id']);
            $nominalDiskon = 0.0;

            if ($komponenModel && $tahunAkademik) {
                $nominalDiskon = $beasiswaService->hitungDiskonUntukKomponen(
                    mahasiswa: $mhs,
                    komponen: $komponenModel,
                    tahunAkademik: $tahunAkademik,
                    nominalDasar: $nominalDasar,
                );
            }

            // Idempotent by design: unique key (tagihan_id, komponen_biaya_id)
            // sudah ada di DB (unik_tagihan_komponen). insertOrIgnore aman
            // dipakai berulang kali - kalau baris sudah ada (race dengan
            // proses lain), insert ini diam-diam di-skip oleh MySQL tanpa
            // exception, dan kita tidak menghitungnya dobel ke total header.
            $affected = DB::table('tagihan_mahasiswas_details')->insertOrIgnore([[
                'tagihan_id' => $tagihan->id,
                'komponen_biaya_id' => $item['komponen_biaya_id'],
                'nama_komponen_snapshot' => $item['nama_komponen'],
                'nominal_dasar' => $nominalDasar,
                'nominal_diskon' => $nominalDiskon,
                'nominal_terbayar' => 0.00,
                'sumber' => 'SINKRONISASI',
                'created_at' => now(),
                'updated_at' => now(),
            ]]);

            if ($affected > 0) {
                $nominalBersih = max(0, $nominalDasar - $nominalDiskon);
                $totalDitambahkan += $nominalBersih;

                $ledger->recordTagihan(
                    mahasiswaId: $mhs->id,
                    nominal: number_format($nominalBersih, 2, '.', ''),
                    // Referensi granular PER DETAIL (bukan per header) supaya
                    // tidak bentrok dengan unique key ledger
                    // (referensi_dokumen, tipe_transaksi) milik entri TAGIHAN
                    // header yang sudah dibuat Generator.
                    referensiDokumen: "sinkronisasi-tagihan:{$tagihan->id}:{$item['komponen_biaya_id']}",
                    keterangan: "Penambahan komponen {$item['nama_komponen']} via Sinkronisasi Tagihan - NIM {$mhs->nim}",
                );
            }
        }

        return number_format($totalDitambahkan, 2, '.', '');
    }

    /**
     * Idempotency untuk review: kalau baris terbuka (PENDING/IN_PROGRESS)
     * untuk tagihan_detail_id yang sama sudah ada, JANGAN insert baris
     * baru - update nominal_skema_baru-nya saja kalau ternyata berubah
     * lagi sejak temuan terakhir. Dikunci per baris supaya dua batch
     * sinkronisasi yang kebetulan jalan bersamaan untuk mahasiswa yang
     * sama tidak menghasilkan 2 baris review terbuka untuk komponen yang
     * sama.
     */
    private function prosesToReview(array $toReview, int $batchId, string $tagihanId, string $mahasiswaId): int
    {
        $jumlahBaru = 0;

        foreach ($toReview as $item) {
            $existingOpen = SinkronisasiReviewItem::where('tagihan_detail_id', $item['tagihan_detail_id'])
                ->whereIn('status', ['PENDING', 'IN_PROGRESS'])
                ->lockForUpdate()
                ->first();

            if ($existingOpen !== null) {
                if (abs((float) $existingOpen->nominal_skema_baru - $item['nominal_skema_baru']) > 0.005) {
                    $existingOpen->update(['nominal_skema_baru' => $item['nominal_skema_baru']]);
                }
                continue;
            }

            SinkronisasiReviewItem::create([
                'sinkronisasi_batch_id' => $batchId,
                'tagihan_id' => $tagihanId,
                'tagihan_detail_id' => $item['tagihan_detail_id'],
                'mahasiswa_id' => $mahasiswaId,
                'komponen_biaya_id' => $item['komponen_biaya_id'],
                'nominal_existing' => $item['nominal_existing'],
                'nominal_skema_baru' => $item['nominal_skema_baru'],
                'status' => 'PENDING',
            ]);
            $jumlahBaru++;
        }

        return $jumlahBaru;
    }

    private function logMahasiswa(int $batchId, string $mahasiswaId, string $status, int $ditambah, int $review, int $warning, string $pesan): void
    {
        DB::table('sinkronisasi_logs')->insert([
            'sinkronisasi_batch_id' => $batchId,
            'mahasiswa_id' => $mahasiswaId,
            'status' => $status,
            'jumlah_ditambah' => $ditambah,
            'jumlah_review' => $review,
            'jumlah_warning' => $warning,
            'pesan' => $pesan,
            'created_at' => now(),
        ]);
    }

    private function kirimNotifikasi(SinkronisasiBatch $batch, array $counter): void
    {
        if (! $this->userId) {
            return;
        }

        $admin = User::find($this->userId);
        if (! $admin) {
            return;
        }

        $judul = $this->dryRun ? 'Dry Run Sinkronisasi Tagihan Selesai' : 'Sinkronisasi Tagihan Selesai';
        $body = "{$counter['total_ditambah']} komponen ditambahkan, {$counter['total_review']} masuk daftar review, "
            . "{$counter['total_warning']} warning, {$counter['total_dilewati']} dilewati, {$counter['total_error']} error.";

        Notification::make()
            ->title($judul)
            ->body($body)
            ->status($counter['total_error'] > 0 ? 'warning' : 'success')
            ->sendToDatabase($admin);
    }

    public function failed(\Throwable $exception): void
    {
        SinkronisasiBatch::where('id', $this->batchId)->update([
            'status' => 'FAILED',
            'completed_at' => now(),
            'error_message' => $exception->getMessage(),
        ]);

        if (! $this->userId) {
            return;
        }
        $admin = User::find($this->userId);
        if (! $admin) {
            return;
        }

        Notification::make()
            ->title('Sinkronisasi Tagihan Gagal Total')
            ->body('Proses berhenti karena error sistem: ' . $exception->getMessage() . '. Data yang sudah tersinkron tidak hilang (per-mahasiswa transaksional), cek Riwayat Sinkronisasi untuk detail.')
            ->status('danger')
            ->sendToDatabase($admin);
    }
}