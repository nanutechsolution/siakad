<?php

declare(strict_types=1);

namespace App\Services\Oidc;

use App\Models\TrxPegawai;
use App\Models\User;

/**
 * Menyusun klaim OIDC (id_token / userinfo) dari User Siakad.
 *
 * Aturan:
 *  - `sub` = UUID users.id (stabil, tidak pernah berubah).
 *  - Hanya klaim yang di-encode dalam granted scope yang diterbitkan.
 *  - Password/hash/remember_token/NIK tidak pernah masuk klaim.
 */
class OidcClaimsBuilder
{
    public function __construct(
        protected AccountStatusResolver $statusResolver,
    ) {}

    /**
     * @param  array<int, string>  $scopes  scope yang di-otorisasi untuk token ini
     * @return array<string, mixed>
     */
    public function forUser(User $user, array $scopes): array
    {
        $scopes = array_map(strval(...), $scopes);

        $claims = ['sub' => $user->getKey()];

        if ($this->has($scopes, 'profile')) {
            $claims['name'] = $this->resolveName($user);
        }

        if ($this->has($scopes, 'email') && filled($user->email)) {
            $claims['email'] = $user->email;
            $claims['email_verified'] = $user->email_verified_at !== null;
        }

        if ($this->has($scopes, 'siakad_identity')) {
            $claims = [...$claims, ...$this->identityClaims($user)];
        }

        if ($this->has($scopes, 'account_status')) {
            $claims = [...$claims, ...$this->statusResolver->claimFor($user)];
        }

        return $claims;
    }

    /**
     * @param  array<int, string>  $scopes
     */
    protected function has(array $scopes, string $scope): bool
    {
        return in_array($scope, $scopes, true);
    }

    protected function resolveName(User $user): string
    {
        $personName = $user->person?->nama_lengkap;

        if (is_string($personName) && trim($personName) !== '') {
            return trim($personName);
        }

        if (is_string($user->name) && trim($user->name) !== '') {
            return trim($user->name);
        }

        return (string) $user->username;
    }

    /**
     * @return array<string, string>
     */
    protected function identityClaims(User $user): array
    {
        $claims = [];

        if ($user->isMahasiswa()) {
            $nim = $user->mahasiswa?->nim;
            $claims['nim'] = is_string($nim) ? $nim : '';
        }

        if ($user->isDosen()) {
            $claims['nidn'] = (string) ($user->dosen?->nidn ?? '');
        }

        if ($user->person_id !== null) {
            $nip = TrxPegawai::query()
                ->where('person_id', $user->person_id)
                ->value('nip');
            $claims['nip'] = (string) $nip;
        }

        // Username Siakad = NIM/NIDN/NIP yang dipakai untuk login.
        $claims['preferred_username'] = (string) $user->username;

        return array_filter(
            $claims,
            fn ($value) => $value !== '',
            ARRAY_FILTER_USE_VALUE
        );
    }
}
