<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Enums\HR\JenisJabatan;
use App\Models\TrxPersonJabatan;
use Filament\Notifications\Notification;

class PegawaiJabatanService
{
    /**
     * Memeriksa indikasi rangkap jabatan struktural pada unit yang sama.
     * Tidak memblokir proses, hanya memunculkan notifikasi peringatan.
     */
    public function periksaIndikasiRangkapStruktural(TrxPersonJabatan $jabatan): void
    {
        $refJabatan = $jabatan->jabatan;

        // Hanya periksa jika jabatan berjenis STRUKTURAL dan masih menjabat (tanggal_selesai NULL)
        if ($refJabatan && $refJabatan->jenis === JenisJabatan::STRUKTURAL && is_null($jabatan->tanggal_selesai)) {

            $indikasiRangkap = TrxPersonJabatan::where('person_id', $jabatan->person_id)
                ->where('id', '!=', $jabatan->id)
                ->whereNull('tanggal_selesai')
                ->where('fakultas_id', $jabatan->fakultas_id)
                ->where('prodi_id', $jabatan->prodi_id)
                ->whereHas('jabatan', function ($query) {
                    $query->where('jenis', JenisJabatan::STRUKTURAL);
                })
                ->exists();

            if ($indikasiRangkap) {
                Notification::make()
                    ->warning()
                    ->title('Indikasi Rangkap Jabatan')
                    ->body('Pegawai ini memiliki jabatan struktural aktif lainnya di unit yang sama. Harap tinjau kembali legalitas dan status aktif jabatan sebelumnya.')
                    ->persistent()
                    ->send();
            }
        }
    }
}
