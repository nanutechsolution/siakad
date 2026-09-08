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
            // --- 1. TOMBOL UTAMA: GENERATE (YANG HILANG SEBELUMNYA) ---
            Action::make('generate')
                ->label('Mulai Generate / Re-Generate Jadwal')
                ->icon('heroicon-o-cpu-chip')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Mulai Proses Komputasi Skala Besar?')
                ->modalDescription('Karena memproses ratusan jadwal membutuhkan waktu, sistem akan mengerjakannya di latar belakang. Anda bisa merefresh halaman nanti untuk melihat hasilnya.')
                ->hidden(fn($record) => in_array($record->status, ['RUNNING', 'COMMITTED']))
                ->action(function ($record) {

                    // 1. Bersihkan draf lama dan ubah status ke RUNNING
                    $record->results()->delete();
                    $record->update(['status' => 'RUNNING', 'total_generated' => 0, 'total_failed' => 0]);

                    // 2. KEMBALI KE MODE ENTERPRISE: Lempar ke Antrean!
                    \App\Jobs\GenerateJadwalJob::dispatch($record->id);

                    // 3. Notifikasi bahwa tugas sudah dititipkan
                    Notification::make()
                        ->title('Tugas Masuk ke Antrean Server!')
                        ->body('Server sedang memproses jadwal di latar belakang. Status saat ini RUNNING.')
                        ->success()
                        ->send();
                }),

            // --- 2. TOMBOL DARURAT: RESET STATUS ---
            Action::make('reset_status')
                ->label('Tersangkut? Force Reset Status')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset Status ke Draf?')
                ->modalDescription('Gunakan ini hanya jika status terus-menerus RUNNING selama berjam-jam.')
                ->visible(fn($record) => $record->status === 'RUNNING')
                ->action(function ($record) {
                    $record->update([
                        'status' => 'PREVIEW', // Kita set ke PREVIEW agar tombol Generate bisa muncul lagi
                        'failure_reason' => 'Di-reset paksa oleh admin karena tersangkut.'
                    ]);
                    Notification::make()->title('Status berhasil di-reset!')->success()->send();
                }),

            // --- 3. TOMBOL FINAL: PUBLISH KE SIAKAD ---
            Action::make('approveAndPublish')
                ->label('Approve & Publish ke SIAKAD')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Publish Jadwal ke SIAKAD')
                ->modalDescription(function () {
                    return "Apakah Anda yakin jadwal ini sudah final? \n\n" .
                        "⚠️ PERHATIAN: Sistem akan otomatis menghapus jadwal lama pada kelas yang sama (Anti-Duplikat), " .
                        "KECUALI jadwal yang sudah ditandai 'Terkunci' (Locked) di lapangan. Jadwal yang terkunci akan dipertahankan.";
                })
                ->visible(fn() => $this->record->status === 'PREVIEW')
                ->action(function () {
                    $this->commitToProduction();
                }),
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

    protected function getFooterWidgets(): array
    {
        return [
            // \App\Filament\Resources\JadwalGeneratorBatches\Widgets\NativeCalendarWidget::class,
        ];
    }

    // Pastikan widget mendapatkan lebar penuh (full-width)
    public function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }
}
