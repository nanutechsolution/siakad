<?php

namespace App\Filament\Mahasiswa\Concerns;

use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Semua page mahasiswa WAJIB pakai trait ini untuk menentukan "milik siapa"
 * data yang ditampilkan. Jangan pernah ambil mahasiswa_id dari request/query
 * string -- selalu turunkan dari user yang sedang login, supaya satu
 * mahasiswa tidak bisa melihat data mahasiswa lain hanya dengan mengubah URL.
 */
trait ResolvesMahasiswa
{
    protected ?Mahasiswa $cachedMahasiswa = null;

    protected ?RefTahunAkademik $cachedTahunAkademikAktif = null;

    protected bool $tahunAkademikResolved = false;

    /**
     * @throws AccessDeniedHttpException jika user login tidak punya data
     *         person, atau person tersebut tidak/tidak lagi terdaftar
     *         sebagai mahasiswa aktif.
     */
    protected function currentMahasiswa(): Mahasiswa
    {
        if ($this->cachedMahasiswa !== null) {
            return $this->cachedMahasiswa;
        }

        $user = Auth::user();

        if (! $user || ! $user->person_id) {
            throw new AccessDeniedHttpException(
                'Akun Anda belum terhubung dengan data pribadi (person). Silakan hubungi admin akademik.'
            );
        }

        $mahasiswa = Mahasiswa::query()
            ->with(['prodi', 'program'])
            ->where('person_id', $user->person_id)
            ->first();

        if (! $mahasiswa) {
            throw new AccessDeniedHttpException(
                'Akun Anda tidak/tidak lagi terdaftar sebagai mahasiswa. Silakan hubungi admin akademik.'
            );
        }

        return $this->cachedMahasiswa = $mahasiswa;
    }

    /**
     * Tahun akademik aktif "hari ini". Bisa null kalau admin belum
     * mengaktifkan periode apa pun -- SEMUA halaman turunan wajib
     * menangani kasus null ini dengan empty state, jangan asumsikan ada.
     */
    protected function tahunAkademikAktif(): ?RefTahunAkademik
    {
        if ($this->tahunAkademikResolved) {
            return $this->cachedTahunAkademikAktif;
        }

        $this->tahunAkademikResolved = true;

        return $this->cachedTahunAkademikAktif = RefTahunAkademik::query()
            ->aktif()
            ->orderByDesc('tanggal_mulai')
            ->first();
    }
}
