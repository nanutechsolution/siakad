<?php

namespace App\Filament\Resources\JadwalGeneratorBatches\Pages;

use App\Filament\Resources\JadwalGeneratorBatches\JadwalGeneratorBatchResource;
use App\Models\DosenPengampu;
use App\Models\JadwalKuliah;
use App\Models\JadwalKuliahDosen;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;

class ViewJadwalGeneratorBatch extends ViewRecord
{
    protected static string $resource = JadwalGeneratorBatchResource::class;
    protected function getHeaderActions(): array
    {
        return [
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
            // 1. Tarik jadwal yang sukses dicarikan ruang & waktu dari Sandbox
            $results = $this->record->results()->where('is_success', true)->get();

            if ($results->isEmpty()) {
                throw new Exception("Tidak ada jadwal sukses yang bisa dipublish.");
            }

            // --- TAMBAHAN BARU: BERSIHKAN JADWAL LAMA (ANTI-DUPLIKAT) ---
            // Cari kelas apa saja yang terlibat di batch ini
            $kelasIdsInBatch = $results->pluck('kelas_id')->unique()->toArray();

            // Cari jadwal existing di database produksi untuk kelas-kelas tersebut
            $existingJadwals = JadwalKuliah::where('tahun_akademik_id', $this->record->tahun_akademik_id)
                ->whereIn('kelas_id', $kelasIdsInBatch)
                ->where('is_locked', false) // PENTING: Jangan hapus jadwal yang sudah dikunci (locked) manual!
                ->get();

            // Hapus detail dosen dan jadwal induknya
            foreach ($existingJadwals as $jadwalLama) {
                JadwalKuliahDosen::where('jadwal_kuliah_id', $jadwalLama->id)->delete();
                $jadwalLama->delete();
            }
            // ------------------------------------------------------------

            // 2. Simpan jadwal baru ke tabel master jadwal_kuliah
            foreach ($results as $result) {
                // Cek apakah kelas ini sudah punya jadwal terkunci untuk MK yang sama
                // Jika sudah terkunci, lewati (jangan di-insert ulang)
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
                    'is_locked' => false, // Default tidak terkunci saat baru di-publish
                ]);

                // 3. Simpan relasi Dosen Pengampu ke tabel pivot jadwal_kuliah_dosen
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

            // 4. Ubah status batch agar tombol publish hilang
            $this->record->update(['status' => 'COMMITTED']);

            DB::commit();

            Notification::make()
                ->title('Berhasil Publish')
                ->body('Jadwal berhasil diperbarui. Jadwal lama yang tidak terkunci telah digantikan.')
                ->success()
                ->send();
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
