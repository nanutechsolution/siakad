<?php

namespace App\Observers;

use App\Models\PembayaranMahasiswa;
use App\Models\Mahasiswa;
use App\Enums\StatusVerifikasiPembayaran;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PembayaranMahasiswaObserver
{
    public function updated(PembayaranMahasiswa $pembayaran): void
    {
        if ($pembayaran->wasChanged('status_verifikasi_id')) {

            if ($pembayaran->status_verifikasi_id === StatusVerifikasiPembayaran::VERIFIED) {

                $pembayaran->loadMissing(['tagihan.mahasiswa.prodi']);

                $tagihan = $pembayaran->tagihan;
                $mahasiswa = $tagihan->mahasiswa ?? null;

                if ($mahasiswa && Str::startsWith($mahasiswa->nim, 'PMB-')) {

                    try {
                        DB::beginTransaction();

                        $prodi = $mahasiswa->prodi;
                        $angkatanTahun = $mahasiswa->angkatan_id;

                        // =========================================================================
                        // LOGIKA DINAMIS PENENTUAN NOMOR URUT (SKENARIO A vs B)
                        // =========================================================================

                        // TODO: Ganti ini dengan pemanggil settingan dinamis di sistem kamu
                        // Contoh: setting('reset_nim_tahunan') atau config('siakad.reset_nim_tahunan')
                        $kampusSettings = app(\App\Settings\KampusSettings::class);
                        $isResetPerTahun = $kampusSettings->reset_nim_tahunan;

                        if ($isResetPerTahun) {
                            $lastMahasiswa = Mahasiswa::where('prodi_id', $prodi->id)
                                ->where('angkatan_id', $angkatanTahun)
                                ->where('nim', 'NOT LIKE', 'PMB-%')
                                ->orderBy('nim', 'desc')
                                ->first();

                            // Pastikan mengambil 3 digit terakhir secara konsisten
                            $lastSeq = $lastMahasiswa ? (int) substr($lastMahasiswa->nim, -3) : 0;
                            $nextSeq = $lastSeq + 1;
                        } else {
                            $nextSeq = $prodi->last_nim_seq + 1;
                        }

                        // 2. PARSING FORMAT NIM (Memaksa 3 digit)
                        $format = $prodi->format_nim ?? '{THN}{KODE}{NO:3}';

                        $nimAsli = $format;
                        $nimAsli = str_replace('{TAHUN}', $angkatanTahun, $nimAsli);
                        $nimAsli = str_replace('{THN}', substr((string) $angkatanTahun, -2), $nimAsli);
                        $nimAsli = str_replace('{KODE}', $prodi->kode_prodi_internal, $nimAsli);

                        // Logika pemformatan {NO:X} yang lebih aman
                        if (preg_match('/\{NO:(\d+)\}/', $nimAsli, $matches)) {
                            $digitCount = (int) $matches[1];
                            $paddedSeq = str_pad((string) $nextSeq, $digitCount, '0', STR_PAD_LEFT);
                            $nimAsli = str_replace($matches[0], $paddedSeq, $nimAsli);
                        } else {
                            // Fallback jika tidak ada :X, paksa ke 3 digit
                            $nimAsli = str_replace('{NO}', str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT), $nimAsli);
                        }

                        // Eksekusi Update ke Database
                        $mahasiswa->update(['nim' => $nimAsli]);

                        // Selalu update last_nim_seq di prodi sebagai catatan riwayat historis terakhir
                        $prodi->update(['last_nim_seq' => $nextSeq]);

                        $tagihan->update(['status_bayar' => 'LUNAS']);

                        DB::commit();
                        if ($mahasiswa->user) {
                            Notification::make()
                                ->title('Selamat! NIM Anda Telah Terbit')
                                ->body("Pembayaran telah diverifikasi. NIM baru Anda adalah: {$nimAsli}.")
                                ->icon('heroicon-o-academic-cap')
                                ->persistent()
                                ->actions([
                                    Action::make('aktifkan_nim')
                                        ->label('Aktifkan NIM Baru')
                                        ->color('success')
                                        ->button()
                                        ->url(url('/mahasiswa/reauth?nim=' . $nimAsli), shouldOpenInNewTab: false),
                                ])
                                ->sendToDatabase($mahasiswa->user); // Sekarang sudah aman karena sudah dicek
                        } else {
                            Log::warning("Gagal kirim notifikasi: Mahasiswa ID {$mahasiswa->id} tidak memiliki akun User.");
                        }

                        Log::info("NIM Generated: Camaba {$mahasiswa->person_id} resmi menjadi mahasiswa dengan NIM {$nimAsli}");
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Gagal Auto-Generate NIM Camaba {$mahasiswa->id}: " . $e->getMessage());
                        throw $e;
                    }
                }
            }
        }
    }
}
