<?php

declare(strict_types=1);

namespace App\Services\Keuangan;

use App\Jobs\ExportSinkronisasiPreviewJob;
use App\Jobs\SinkronisasiTagihanJob;
use App\Models\KeuanganAdjustment;
use App\Models\Mahasiswa;
use App\Models\SinkronisasiBatch;
use App\Models\SinkronisasiReviewItem;
use App\Models\TagihanMahasiswaDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SinkronisasiTagihanService
{
    /** Batas jumlah baris detail yang ditampilkan penuh di UI preview. */
    private const PREVIEW_DISPLAY_LIMIT = 100;

    public function __construct(
        private readonly TargetMahasiswaResolver $resolver,
        private readonly KomponenTagihanComparator $comparator,
    ) {}

    /**
     * Preview SINKRON (tanpa queue) - dipanggil langsung dari halaman
     * Filament setiap parameter berubah. Read-only murni, tidak menulis
     * apa pun ke DB. Mengembalikan agregat lengkap (akurat untuk SELURUH
     * target, dihitung via chunk supaya hemat memori) + sampel baris untuk
     * ditampilkan di tabel UI (dibatasi PREVIEW_DISPLAY_LIMIT).
     */
    public function preview(array $data): array
    {
        $agregat = [
            'total_mahasiswa_target' => 0,
            'total_tanpa_tagihan' => 0, // dilewati: belum punya tagihan reguler sama sekali
            'total_tanpa_skema' => 0,   // dilewati: skema tarif belum dikonfigurasi
            'jumlah_ditambah' => 0,
            'jumlah_review' => 0,
            'jumlah_warning' => 0,
            'jumlah_tidak_berubah' => 0,
        ];

        $sampelToAdd = [];
        $sampelToReview = [];
        $sampelToWarn = [];

        $this->iterasiTarget($data, function (Mahasiswa $mhs, ?object $skemaTarif, $detailSkema, ?object $tagihan, $detailExisting) use (
            &$agregat,
            &$sampelToAdd,
            &$sampelToReview,
            &$sampelToWarn
        ) {
            $agregat['total_mahasiswa_target']++;

            if ($tagihan === null) {
                $agregat['total_tanpa_tagihan']++;
                return;
            }

            if ($skemaTarif === null) {
                $agregat['total_tanpa_skema']++;
                return;
            }

            $hasil = $this->comparator->bandingkan($detailSkema, $detailExisting);
            $ringkasan = $hasil->toSummaryArray();

            $agregat['jumlah_ditambah'] += $ringkasan['jumlah_ditambah'];
            $agregat['jumlah_review'] += $ringkasan['jumlah_review'];
            $agregat['jumlah_warning'] += $ringkasan['jumlah_warning'];
            $agregat['jumlah_tidak_berubah'] += $ringkasan['jumlah_tidak_berubah'];

            foreach ($hasil->toAdd as $row) {
                if (count($sampelToAdd) < self::PREVIEW_DISPLAY_LIMIT) {
                    $sampelToAdd[] = [...$row, 'nim' => $mhs->nim, 'nama' => $mhs->person?->nama_lengkap];
                }
            }
            foreach ($hasil->toReview as $row) {
                if (count($sampelToReview) < self::PREVIEW_DISPLAY_LIMIT) {
                    $sampelToReview[] = [...$row, 'nim' => $mhs->nim, 'nama' => $mhs->person?->nama_lengkap];
                }
            }
            foreach ($hasil->toWarn as $row) {
                if (count($sampelToWarn) < self::PREVIEW_DISPLAY_LIMIT) {
                    $sampelToWarn[] = [...$row, 'nim' => $mhs->nim, 'nama' => $mhs->person?->nama_lengkap];
                }
            }
        });

        return [
            'agregat' => $agregat,
            'sampel_tambah' => $sampelToAdd,
            'sampel_review' => $sampelToReview,
            'sampel_warning' => $sampelToWarn,
            'dibatasi' => $agregat['jumlah_ditambah'] > self::PREVIEW_DISPLAY_LIMIT
                || $agregat['jumlah_review'] > self::PREVIEW_DISPLAY_LIMIT
                || $agregat['jumlah_warning'] > self::PREVIEW_DISPLAY_LIMIT,
        ];
    }

    /**
     * Mendaftarkan permintaan export preview lengkap (tanpa batas
     * PREVIEW_DISPLAY_LIMIT) dan melemparnya ke queue
     * (ExportSinkronisasiPreviewJob) - untuk target dengan jumlah mahasiswa
     * besar, generate CSV di request thread bisa timeout / memblokir UI,
     * jadi sama seperti pola imports/exports lain di sistem Anda, prosesnya
     * async dan admin dapat notifikasi berisi link unduh saat selesai.
     *
     * Tidak menulis apa pun ke tagihan - job ini murni baca & bandingkan.
     */
    public function requestExportPreview(array $data, ?string $userId): array
    {
        $exportId = DB::table('exports')->insertGetId([
            'exporter' => self::class . '@exportPreviewCsv',
            'file_disk' => 'local',
            'file_name' => null,
            'processed_rows' => 0,
            'total_rows' => 0,
            'successful_rows' => 0,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ExportSinkronisasiPreviewJob::dispatch($exportId, $data, $userId);

        return [
            'export_id' => $exportId,
            'message' => 'Export preview dimasukkan ke antrean. Link unduh akan dikirim lewat notifikasi begitu selesai.',
        ];
    }

    /**
     * Membuat batch dan men-dispatch job. Kalau $dryRun true, job tetap
     * dijalankan (supaya prosesnya identik dengan eksekusi sungguhan dan
     * hasilnya reproducible) tapi TIDAK menulis perubahan apa pun ke
     * tagihan_mahasiswas_details / sinkronisasi_review_items - hanya
     * mengisi summary_snapshot batch. Job sendiri yang menegakkan
     * pembatasan ini (lihat SinkronisasiTagihanJob::handle()).
     */
    public function jalankan(array $data, ?string $userId, bool $dryRun = false): array
    {
        $batch = SinkronisasiBatch::create([
            'tahun_akademik_id' => $data['tahun_akademik_id'],
            'mode' => $dryRun ? 'DRY_RUN' : 'EKSEKUSI',
            'status' => 'PROCESSING',
            'parameter_snapshot' => $data,
            'started_at' => now(),
            'created_by' => $userId,
        ]);

        SinkronisasiTagihanJob::dispatch($batch->id, $data, $userId, $dryRun);

        return [
            'status' => 'success',
            'batch_id' => $batch->id,
            'message' => $dryRun
                ? 'Dry run dimasukkan ke antrean. Hasil akan tersedia di Riwayat Sinkronisasi tanpa mengubah data apa pun.'
                : 'Proses sinkronisasi telah dimasukkan ke antrean sistem.',
        ];
    }

    /**
     * Menindaklanjuti satu atau beberapa temuan review menjadi Adjustment
     * resmi lewat modul Adjustment yang sudah ada. Dieksekusi langsung
     * (bukan lewat queue) karena merupakan aksi kecil per-klik admin.
     *
     * Locking: setiap baris dikunci (`lockForUpdate`) di dalam transaksi
     * dan statusnya diverifikasi ulang SETELAH lock didapat - bukan
     * sebelumnya - supaya dua admin yang mengklik "Ajukan" pada baris yang
     * sama nyaris bersamaan tidak berdua-duanya berhasil membuat
     * Adjustment. Yang datang belakangan akan melihat status sudah bukan
     * PENDING lagi (baris di-skip, dilaporkan di hasil).
     */
    public function ajukanKeAdjustment(array $reviewItemIds, string $userId): array
    {
        $dibuat = [];
        $dilewati = [];

        DB::transaction(function () use ($reviewItemIds, $userId, &$dibuat, &$dilewati) {
            $items = SinkronisasiReviewItem::whereIn('id', $reviewItemIds)
                ->lockForUpdate()
                ->get();

            foreach ($items as $item) {
                if ($item->status !== 'PENDING') {
                    $dilewati[] = [
                        'id' => $item->id,
                        'alasan' => "Sudah berstatus {$item->status} (kemungkinan diproses admin lain).",
                    ];
                    continue;
                }

                $adjustment = KeuanganAdjustment::create([
                    'nomor_adjustment' => 'ADJ-SYNC-' . date('Ymd') . '-' . Str::upper(Str::random(6)),
                    'tagihan_id' => $item->tagihan_id,
                    'jenis_adjustment' => 'KOREKSI_NOMINAL_SINKRONISASI',
                    'nominal' => $item->selisih(),
                    'keterangan' => "Hasil Sinkronisasi Tagihan: komponen #{$item->komponen_biaya_id} "
                        . "berubah dari {$item->nominal_existing} menjadi {$item->nominal_skema_baru}.",
                    'status' => 'DRAFT',
                    'created_by' => $userId,
                ]);

                $item->update([
                    'status' => 'RESOLVED',
                    'keuangan_adjustment_id' => $adjustment->id,
                    'resolved_by' => $userId,
                    'resolved_at' => now(),
                ]);

                $dibuat[] = ['review_item_id' => $item->id, 'adjustment_id' => $adjustment->id];
            }
        });

        return ['dibuat' => $dibuat, 'dilewati' => $dilewati];
    }

    /**
     * Iterator bersama untuk preview & export - memastikan keduanya
     * mengevaluasi mahasiswa dengan cara yang identik dengan job eksekusi.
     * $callback menerima (Mahasiswa, ?skemaTarif, detailSkema, ?tagihan, detailExisting).
     */
    public function iterasiTarget(array $data, callable $callback): void
    {
        $tahunAkademikId = $data['tahun_akademik_id'];

        $query = $this->resolver->resolve($data);

        $query->chunkById(200, function ($mahasiswaChunk) use ($callback, $tahunAkademikId) {
            foreach ($mahasiswaChunk as $mhs) {
                // Sinkronisasi hanya relevan untuk mahasiswa yang SUDAH
                // punya tagihan reguler di semester ini (kebalikan dari
                // Generator, yang justru skip kalau sudah ada).
                $tagihan = DB::table('tagihan_mahasiswas')
                    ->where('mahasiswa_id', $mhs->id)
                    ->where('tahun_akademik_id', $tahunAkademikId)
                    ->first();

                if ($tagihan === null) {
                    $callback($mhs, null, collect(), null, collect());
                    continue;
                }

                if (empty($mhs->program_id)) {
                    $callback($mhs, null, collect(), (object) (array) $tagihan, collect());
                    continue;
                }

                $skemaTarif = DB::table('keuangan_skema_tarif')
                    ->where('angkatan_id', $mhs->angkatan_id)
                    ->where('prodi_id', $mhs->prodi_id)
                    ->where('program_kelas_id', $mhs->program_id)
                    ->where('is_active', 1)
                    ->first();

                if ($skemaTarif === null) {
                    $callback($mhs, null, collect(), (object) (array) $tagihan, collect());
                    continue;
                }

                $detailSkema = DB::table('keuangan_detail_tarif')
                    ->join('keuangan_komponen_biaya', 'keuangan_komponen_biaya.id', '=', 'keuangan_detail_tarif.komponen_biaya_id')
                    ->where('keuangan_detail_tarif.skema_tarif_id', $skemaTarif->id)
                    ->select('keuangan_detail_tarif.*', 'keuangan_komponen_biaya.nama_komponen')
                    ->get();

                $detailExisting = TagihanMahasiswaDetail::where('tagihan_id', $tagihan->id)->get();

                $callback($mhs, (object) (array) $skemaTarif, $detailSkema, (object) (array) $tagihan, $detailExisting);
            }
        }, column: 'mahasiswas.id');
    }
}
