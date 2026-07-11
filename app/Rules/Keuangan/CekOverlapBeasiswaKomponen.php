<?php

declare(strict_types=1);

namespace App\Rules\Keuangan;

use App\Models\KeuanganMahasiswaBeasiswa;
use App\Models\KeuanganMasterBeasiswa;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CekOverlapBeasiswaKomponen implements ValidationRule
{
    public function __construct(
        protected string $mahasiswaId,
        protected int $beasiswaIdDiusulkan,
        protected bool $isOverlapAllowed = false,
        protected ?int $ignoreRecordId = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Jika admin secara eksplisit mengizinkan overlap lewat toggle form, skip validasi
        if ($this->isOverlapAllowed) {
            return;
        }

        $beasiswaBaru = KeuanganMasterBeasiswa::with('details')->find($this->beasiswaIdDiusulkan);
        if (! $beasiswaBaru) {
            return;
        }

        $komponenDiambil = $beasiswaBaru->details->pluck('komponen_biaya_id')->toArray();
        if (empty($komponenDiambil)) {
            return;
        }

        // Cari beasiswa aktif lain milik mahasiswa ini
        $beasiswaAktifLainnya = KeuanganMahasiswaBeasiswa::with(['beasiswa.details'])
            ->where('mahasiswa_id', $this->mahasiswaId)
            ->where('is_active', true)
            ->when($this->ignoreRecordId, fn($q) => $q->where('id', '!=', $this->ignoreRecordId))
            ->get();

        foreach ($beasiswaAktifLainnya as $beasiswaAktif) {
            if (! $beasiswaAktif->beasiswa) continue;

            $komponenAktif = $beasiswaAktif->beasiswa->details->pluck('komponen_biaya_id')->toArray();

            // Cek irisan komponen biaya (apakah ada komponen yang sama-sama didiskon)
            $overlapKomponen = array_intersect($komponenDiambil, $komponenAktif);

            if (count($overlapKomponen) > 0) {
                $fail("Mahasiswa ini sudah menerima diskon pada komponen biaya yang sama dari program beasiswa: {$beasiswaAktif->beasiswa->nama_beasiswa}. Centang opsi 'Izinkan Overlap' jika Anda yakin ini diperbolehkan.");
                return;
            }
        }
    }
}
