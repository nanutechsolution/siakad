<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Services\Keuangan\SinkronisasiTagihanService;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Export preview Sinkronisasi ke CSV, dijalankan di background - berbeda
 * dari SinkronisasiTagihanJob, job ini TIDAK menulis apa pun ke tagihan
 * (murni membaca & membandingkan, sama seperti SinkronisasiTagihanService::preview()),
 * hanya saja tanpa batas 100 baris per kategori yang dipakai UI.
 *
 * Menggunakan tabel `exports` yang sudah ada di skema Anda (mengikuti
 * konvensi field yang sama seperti fitur import/export bawaan lainnya di
 * sistem Anda) supaya progres & histori export bisa dipantau lewat cara
 * yang konsisten dengan modul lain, walau job ini custom (bukan lewat
 * pipeline Filament\Actions\Exports bawaan, karena data preview di sini
 * bukan baris tabel Eloquent, melainkan hasil perbandingan on-the-fly).
 */
class ExportSinkronisasiPreviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;
    public int $tries = 1;

    public function __construct(
        protected int $exportId,
        protected array $data,
        protected ?string $userId,
    ) {}

    public function handle(SinkronisasiTagihanService $service): void
    {
        $fileName = 'sinkronisasi-preview-' . date('Ymd-His') . '-' . Str::random(6) . '.csv';
        $disk = 'local';
        $relativePath = 'exports/' . $fileName;

        $totalRows = 0;

        try {
            $absolutePath = storage_path('app/' . $relativePath);
            if (! is_dir(dirname($absolutePath))) {
                mkdir(dirname($absolutePath), recursive: true);
            }

            $handle = fopen($absolutePath, 'w');
            fputcsv($handle, ['kategori', 'nim', 'nama', 'komponen', 'nominal_existing', 'nominal_baru']);

            $service->iterasiTarget($this->data, function ($mhs, $skemaTarif, $detailSkema, $tagihan, $detailExisting) use ($handle, &$totalRows, $service) {
                if ($tagihan === null || $skemaTarif === null) {
                    return;
                }

                $hasil = app(\App\Services\Keuangan\KomponenTagihanComparator::class)->bandingkan($detailSkema, $detailExisting);
                $totalRows++;

                foreach ($hasil->toAdd as $row) {
                    fputcsv($handle, ['TAMBAH', $mhs->nim, $mhs->person?->nama_lengkap, $row['nama_komponen'], '', $row['nominal']]);
                }
                foreach ($hasil->toReview as $row) {
                    fputcsv($handle, ['REVIEW', $mhs->nim, $mhs->person?->nama_lengkap, $row['nama_komponen'], $row['nominal_existing'], $row['nominal_skema_baru']]);
                }
                foreach ($hasil->toWarn as $row) {
                    fputcsv($handle, ['WARNING', $mhs->nim, $mhs->person?->nama_lengkap, $row['nama_komponen_snapshot'], $row['nominal_existing'], '']);
                }
            });

            fclose($handle);

            DB::table('exports')->where('id', $this->exportId)->update([
                'completed_at' => now(),
                'file_disk' => $disk,
                'file_name' => $relativePath,
                'processed_rows' => $totalRows,
                'total_rows' => $totalRows,
                'successful_rows' => $totalRows,
                'updated_at' => now(),
            ]);

            $this->notifikasiSelesai($relativePath);
        } catch (\Throwable $e) {
            DB::table('exports')->where('id', $this->exportId)->update([
                'completed_at' => now(),
                'updated_at' => now(),
            ]);
            $this->notifikasiGagal($e->getMessage());
            throw $e;
        }
    }

    private function notifikasiSelesai(string $relativePath): void
    {
        if (! $this->userId) {
            return;
        }
        $admin = User::find($this->userId);
        if (! $admin) {
            return;
        }

        Notification::make()
            ->title('Export Preview Sinkronisasi Siap Diunduh')
            ->body('File CSV lengkap sudah selesai dibuat.')
            ->success()
            ->actions([
                Notification::make('unduh')
                    ->label('Unduh CSV')
                    ->url(route('sinkronisasi.export.download', ['export' => $this->exportId]), shouldOpenInNewTab: true)
                    ->button(),
            ])
            ->sendToDatabase($admin);
    }

    private function notifikasiGagal(string $pesan): void
    {
        if (! $this->userId) {
            return;
        }
        $admin = User::find($this->userId);
        if (! $admin) {
            return;
        }

        Notification::make()
            ->title('Export Preview Sinkronisasi Gagal')
            ->body($pesan)
            ->danger()
            ->sendToDatabase($admin);
    }
}
