<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Pages;

use App\Filament\Resources\JadwalGeneratorBatches\JadwalGeneratorBatchResource;
use App\Jobs\GenerateJadwalJob;
use App\Models\DosenPengampu;
use App\Models\JadwalKuliah;
use App\Models\JadwalKuliahDosen;
use App\Models\MahasiswaKelas;
use App\Models\RefRuang;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ViewJadwalGeneratorBatch extends ViewRecord
{
    protected static string $resource = JadwalGeneratorBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            /*
         * ============================================================
         * GENERATE / RE-GENERATE
         * ============================================================
         */
            Action::make('generate')
                ->label(
                    fn($record) =>
                    $record->status === 'PREVIEW'
                        ? 'Generate Ulang'
                        : 'Mulai Generate'
                )
                ->icon('heroicon-o-cpu-chip')
                ->color('primary')
                ->size('sm')
                ->requiresConfirmation()
                ->modalHeading(
                    fn($record) =>
                    $record->status === 'PREVIEW'
                        ? 'Generate Ulang Jadwal?'
                        : 'Mulai Generate Jadwal?'
                )
                ->modalDescription(
                    'Sistem akan membuat jadwal secara otomatis berdasarkan '
                        . 'dosen pengampu, kelas, kurikulum, ketersediaan ruang, '
                        . 'waktu operasional, dan konflik jadwal. '
                        . 'Hasil sebelumnya akan dihapus dan dibuat ulang.'
                )
                ->modalSubmitActionLabel('Ya, Mulai Generate')
                ->modalCancelActionLabel('Batal')
                ->hidden(
                    fn($record) =>
                    in_array($record->status, ['RUNNING', 'COMMITTED'], true)
                )
                ->action(function ($record) {

                    $record->results()->delete();

                    $record->update([
                        'status' => 'RUNNING',
                        'total_generated' => 0,
                        'total_failed' => 0,
                        'quality_score' => null,
                        'quality_summary' => null,
                        'failure_reason' => null,
                    ]);

                    GenerateJadwalJob::dispatch($record->id);

                    Notification::make()
                        ->title('Generate dimulai')
                        ->body(
                            'Proses pembuatan jadwal berjalan di background. '
                                . 'Silakan tunggu sampai status berubah menjadi PREVIEW.'
                        )
                        ->success()
                        ->send();
                }),

            /*
         * ============================================================
         * APPROVE & PUBLISH
         * ============================================================
         */
            Action::make('approveAndPublish')
                ->label('Approve & Publish')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->size('sm')
                ->requiresConfirmation()
                ->modalWidth('2xl')
                ->modalHeading('Publish Jadwal ke SIAKAD')
                ->modalDescription(
                    'Periksa ringkasan hasil generate sebelum jadwal dipublikasikan.'
                )
                ->modalContent(function ($record) {
                    $success = $record->results()
                        ->where('status', 'success')
                        ->count();

                    $needsAdjustment = $record->results()
                        ->where('status', 'needs_adjustment')
                        ->count();

                    $failed = $record->results()
                        ->whereIn('status', [
                            'master_failure',
                            'critical_conflict',
                        ])
                        ->count();

                    $score = filled($record->quality_score)
                        ? number_format($record->quality_score, 1)
                        : '-';

                    return new \Illuminate\Support\HtmlString(
                        <<<HTML
            <div class="space-y-5">

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Jadwal Berhasil
                        </div>

                        <div class="mt-1 text-2xl font-bold text-success-600">
                            {$success}
                        </div>

                        <div class="mt-1 text-xs text-gray-500">
                            siap dipublish
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Perlu Penyesuaian
                        </div>

                        <div class="mt-1 text-2xl font-bold text-warning-600">
                            {$needsAdjustment}
                        </div>

                        <div class="mt-1 text-xs text-gray-500">
                            belum dipublish
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Skor Kualitas
                        </div>

                        <div class="mt-1 text-2xl font-bold text-primary-600">
                            {$score}
                            <span class="text-sm font-medium text-gray-500">
                                / 100
                            </span>
                        </div>

                        <div class="mt-1 text-xs text-gray-500">
                            kualitas hasil generate
                        </div>
                    </div>

                </div>

                <div class="rounded-xl border border-primary-200 bg-primary-50 p-4 dark:border-primary-800 dark:bg-primary-950/30">
                    <div class="flex gap-3">

                        <div class="mt-0.5 shrink-0">
                            <svg
                                class="h-5 w-5 text-primary-600"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.8"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 9v3.75m0 3.75h.007v.008H12v-.008ZM10.34 3.94 2.69 17.25A1.5 1.5 0 0 0 3.99 19.5h16.02a1.5 1.5 0 0 0 1.3-2.25L13.66 3.94a1.91 1.91 0 0 0-3.32 0Z"
                                />
                            </svg>
                        </div>

                        <div>
                            <div class="font-semibold text-primary-900 dark:text-primary-100">
                                Yang akan terjadi saat Publish
                            </div>

                            <ul class="mt-2 space-y-1.5 text-sm text-primary-800 dark:text-primary-200">
                                <li>• {$success} jadwal berhasil akan masuk ke SIAKAD.</li>
                                <li>• Jadwal lama pada kelas yang sama akan digantikan.</li>
                                <li>• Jadwal yang sudah <strong>terkunci</strong> tidak akan dihapus.</li>
                                <li>• Jadwal yang masih perlu penyesuaian tidak akan dipublish.</li>
                            </ul>
                        </div>

                    </div>
                </div>
                <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                    Pastikan hasil pada tab <strong>Preview</strong> sudah diperiksa
                    sebelum melanjutkan.
                </div>

            </div>
            HTML
                    );
                })
                ->modalSubmitActionLabel('Ya, Publish ke SIAKAD')
                ->modalCancelActionLabel('Kembali ke Preview')
                ->visible(fn($record) => $record->status === 'PREVIEW')
                ->action(function () {
                    $this->commitToProduction();
                }),

            /*
         * ============================================================
         * MORE / DARURAT
         * ============================================================
         */
            ActionGroup::make([
                Action::make('reset_status')
                    ->label('Reset Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Batch?')
                    ->modalDescription(
                        'Gunakan hanya jika proses generate berhenti '
                            . 'terlalu lama pada status RUNNING. '
                            . 'Reset tidak menjalankan ulang proses generate.'
                    )
                    ->modalSubmitActionLabel('Ya, Reset Status')
                    ->modalCancelActionLabel('Batal')
                    ->visible(
                        fn($record) =>
                        $record->status === 'RUNNING'
                    )
                    ->action(function ($record) {

                        $record->update([
                            'status' => 'PREVIEW',
                            'failure_reason' =>
                            'Status di-reset secara manual oleh admin.',
                        ]);

                        Notification::make()
                            ->title('Status berhasil di-reset')
                            ->body(
                                'Batch dikembalikan ke PREVIEW. '
                                    . 'Anda dapat menjalankan Generate kembali.'
                            )
                            ->success()
                            ->send();
                    }),
            ])
                ->label('Lainnya')
                ->icon('heroicon-o-ellipsis-vertical')
                ->color('gray')
                ->button(),
        ];
    }

    /**
     * Memindahkan data dari Sandbox (Results) ke Production (Jadwal Kuliah)
     */
    protected function commitToProduction(): void
    {
        DB::beginTransaction();

        try {
            $results = $this->record->results()->where('is_success', true)->get();

            if ($results->isEmpty()) {
                throw new Exception("Tidak ada jadwal sukses yang bisa dipublish.");
            }

            // Bersihkan jadwal lama (Anti-Duplikat)
            $kelasIdsInBatch = $results->pluck('kelas_id')->unique()->toArray();

            $existingJadwals = JadwalKuliah::where('tahun_akademik_id', $this->record->tahun_akademik_id)
                ->whereIn('kelas_id', $kelasIdsInBatch)
                ->where('is_locked', false)
                ->get();

            foreach ($existingJadwals as $jadwalLama) {
                JadwalKuliahDosen::where('jadwal_kuliah_id', $jadwalLama->id)->delete();
                $jadwalLama->delete();
            }

            // Simpan jadwal baru 
            foreach ($results as $result) {
                $isAlreadyLocked = JadwalKuliah::where('kelas_id', $result->kelas_id)
                    ->where('mata_kuliah_id', $result->mata_kuliah_id)
                    ->where('is_locked', true)
                    ->exists();

                if ($isAlreadyLocked) continue;

                $jadwalBaru = JadwalKuliah::create([
                    'tahun_akademik_id' => $this->record->tahun_akademik_id,
                    'mata_kuliah_id' => $result->mata_kuliah_id,
                    'kelas_id' => $result->kelas_id,
                    'ruang_id' => $result->ruang_id,
                    'hari' => $result->hari,
                    'jam_mulai' => $result->jam_mulai,
                    'jam_selesai' => $result->jam_selesai,
                    'kuota_kelas' => $result->estimasi_kapasitas_dibutuhkan,
                    'is_locked' => false,
                ]);

                $dosenPengampuIds = $result->dosen_pengampu_ids ?? [];

                if (!empty($dosenPengampuIds)) {
                    $pengampus = DosenPengampu::whereIn('id', $dosenPengampuIds)->get();

                    foreach ($pengampus as $pengampu) {
                        JadwalKuliahDosen::create([
                            'jadwal_kuliah_id' => $jadwalBaru->id,
                            'dosen_id' => $pengampu->dosen_id,
                            'is_koordinator' => $pengampu->is_koordinator,
                            'is_penilai' => true,
                        ]);
                    }
                }
            }

            $this->record->update(['status' => 'COMMITTED']);
            DB::commit();

            Notification::make()
                ->title('Berhasil Publish')
                ->body('Jadwal berhasil diperbarui. Jadwal lama yang tidak terkunci telah digantikan.')
                ->success()
                ->send();

            redirect(request()->header('Referer')); // Auto-refresh setelah publish

        } catch (Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Gagal Publish')
                ->body('Terjadi kesalahan sistem: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
