<?php

declare(strict_types=1);

namespace App\Services\Oidc;

use App\Enums\StatusKuliah;
use App\Models\Mahasiswa;
use App\Models\RefTahunAkademik;
use App\Models\RiwayatStatusMahasiswa;
use App\Models\TrxPegawai;
use App\Models\User;

/**
 * Menentukan status akun aktif/nonaktif untuk klaim OIDC.
 *
 * Sebuah user dianggap aktif hanya jika:
 *  - users.is_active = true, dan
 *  - record akademik/kepegawaian terkait (jika ada) juga aktif.
 *
 * Untuk mahasiswa, status kuliah diambil dari riwayat pada tahun akademik
 * yang sedang aktif (kolom mahasiswas.sendiri tidak menyimpan status).
 * Mahasiswa tanpa riwayat pada tahun aktif dianggap nonaktif.
 */
class AccountStatusResolver
{
    /**
     * Apakah user dianggap layak memperoleh token SSO.
     */
    public function isActive(User $user): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->isMahasiswa()) {
            return $this->mahasiswaIsActive($user->mahasiswa);
        }

        if ($user->isDosen()) {
            return (bool) $user->dosen?->is_active;
        }

        $pegawai = $this->pegawaiFor($user);

        if ($pegawai !== null) {
            return (bool) $pegawai->is_active;
        }

        // User tanpa record akademik/kepegawaian (mis. admin murni):
        // cukup is_active di users.
        return true;
    }

    protected function mahasiswaIsActive(?Mahasiswa $mahasiswa): bool
    {
        if ($mahasiswa === null) {
            return false;
        }

        $tahunAktifId = $this->activeTahunAkademikId();

        if ($tahunAktifId === null) {
            // Fail-closed: tanpa tahun akademik aktif tidak ada dasar untuk
            // menilai status kuliah, maka SSO ditolak (sesuai dokumentasi
            // docs/SSO_OIDC.md). Kebijakan ini mengikuti dokumen, bukan
            // mengizinkan mahasiswa hanya berdasarkan users.is_active.
            return false;
        }

        $status = RiwayatStatusMahasiswa::query()
            ->where('mahasiswa_id', $mahasiswa->getKey())
            ->where('tahun_akademik_id', $tahunAktifId)
            ->latest('id')
            ->first();

        if ($status === null) {
            return false;
        }

        return $status->status_kuliah === StatusKuliah::AKTIF->value;
    }

    protected function pegawaiFor(User $user): ?TrxPegawai
    {
        if ($user->person_id === null) {
            return null;
        }

        return TrxPegawai::query()
            ->where('person_id', $user->person_id)
            ->orderByDesc('is_active')
            ->first();
    }

    protected function activeTahunAkademikId(): ?int
    {
        $id = RefTahunAkademik::query()
            ->where('is_active', true)
            // Semestinya hanya satu tahun aktif; orderByDesc menjamin
            // determinisme bila data menyimpang (tanpa ORDER BY MySQL boleh
            // mengembalikan baris mana pun).
            ->orderByDesc('id')
            ->value('id');

        return $id === null ? null : (int) $id;
    }

    /**
     * Klaim ringkas untuk id_token/userinfo (hanya bila scope account_status).
     *
     * @return array<string, bool>
     */
    public function claimFor(User $user): array
    {
        return ['active' => $this->isActive($user)];
    }
}
