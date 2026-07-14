<?php

namespace App\Observers;

use App\Models\PembayaranMahasiswa;
use App\Models\Mahasiswa;
use App\Enums\StatusVerifikasiPembayaran;
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
                            // SKENARIO B: Reset nomor urut ke 1 setiap ganti tahun angkatan
                            $lastMahasiswa = Mahasiswa::where('prodi_id', $prodi->id)
                                ->where('angkatan_id', $angkatanTahun)
                                ->where('nim', 'NOT LIKE', 'PMB-%')
                                ->orderBy('nim', 'desc')
                                ->first();

                            if ($lastMahasiswa) {
                                // Ekstrak angka urutan di ekor NIM mahasiswa terakhir (Misal dari "26010045" diambil "45")
                                preg_match('/(\d+)$/', $lastMahasiswa->nim, $matches);
                                $lastSeq = $matches ? (int) $matches[1] : 0;
                                $nextSeq = $lastSeq + 1;
                            } else {
                                // Jika belum ada mahasiswa di angkatan ini sama sekali
                                $nextSeq = 1;
                            }
                        } else {
                            // SKENARIO A: Lanjut terus menerus berdasarkan data di ref_prodi
                            $nextSeq = $prodi->last_nim_seq + 1;
                        }
                        // =========================================================================

                        // Parsing Format NIM (Misal: {THN}{KODE}{NO:4})
                        $format = $prodi->format_nim ?? '{THN}{KODE}{NO:4}';

                        $nimAsli = $format;
                        $nimAsli = str_replace('{TAHUN}', $angkatanTahun, $nimAsli);
                        $nimAsli = str_replace('{THN}', substr((string) $angkatanTahun, -2), $nimAsli);
                        $nimAsli = str_replace('{KODE}', $prodi->kode_prodi_internal, $nimAsli);

                        if (preg_match('/\{NO:(\d+)\}/', $nimAsli, $matches)) {
                            $digitCount = (int) $matches[1];
                            $paddedSeq = str_pad((string) $nextSeq, $digitCount, '0', STR_PAD_LEFT);
                            $nimAsli = str_replace($matches[0], $paddedSeq, $nimAsli);
                        } else {
                            // Fallback jika tidak ada jumlah digit khusus
                            $nimAsli = str_replace('{NO}', str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT), $nimAsli);
                        }

                        // Eksekusi Update ke Database
                        $mahasiswa->update(['nim' => $nimAsli]);

                        // Selalu update last_nim_seq di prodi sebagai catatan riwayat historis terakhir
                        $prodi->update(['last_nim_seq' => $nextSeq]);

                        $tagihan->update(['status_bayar' => 'LUNAS']);

                        DB::commit();

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
