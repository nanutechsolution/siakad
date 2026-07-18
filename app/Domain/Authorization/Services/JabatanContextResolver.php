<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Services;

use App\Domain\Authorization\ValueObjects\OrganizationContext;
use App\Models\TrxPersonJabatan;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Mengelola "konteks kerja aktif" seorang user: jabatan mana (dari
 * trx_person_jabatan yang sedang aktif) yang sedang dipakai untuk
 * request saat ini. Disimpan di session, bukan DB, karena sifatnya
 * per-sesi login, bukan data permanen.
 */
final class JabatanContextResolver
{
    private const SESSION_KEY = 'org_context.jabatan_row_id';

    public function __construct(
        private readonly Session $session,
    ) {}

    /**
     * Seluruh trx_person_jabatan yang sedang aktif (tanggal_mulai <= today
     * AND (tanggal_selesai IS NULL OR tanggal_selesai >= today)) milik
     * person yang terhubung ke user ini.
     */
    public function availableContexts(User $user): Collection
    {
        if ($user->person_id === null) {
            return collect();
        }

        $today = now()->toDateString();

        return TrxPersonJabatan::query()
            ->with(['jabatan', 'prodi', 'fakultas'])
            ->where('person_id', $user->person_id)
            ->where('tanggal_mulai', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', $today);
            })
            ->get();
    }

    /**
     * True jika user punya lebih dari satu jabatan aktif dan belum memilih
     * konteks mana yang sedang dipakai pada sesi ini.
     */
    public function requiresSelection(User $user): bool
    {
        if ($this->session->get(self::SESSION_KEY) !== null) {
            return false;
        }

        return $this->availableContexts($user)->count() > 1;
    }

    public function setActiveContext(User $user, int $trxPersonJabatanId): void
    {
        $valid = $this->availableContexts($user)->firstWhere('id', $trxPersonJabatanId);

        abort_unless($valid !== null, 403, 'Jabatan tersebut bukan konteks aktif Anda.');

        $this->session->put(self::SESSION_KEY, $trxPersonJabatanId);
    }

    public function clearContext(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }

    /**
     * Resolve OrganizationContext untuk request saat ini. Jika user hanya
     * punya 1 jabatan aktif, otomatis dipakai tanpa perlu pilih manual.
     */
    public function current(User $user): OrganizationContext
    {
        $contexts = $this->availableContexts($user);
        $roleNames = $user->getRoleNames()->all();
        $isGlobal = collect($roleNames)
            ->intersect(config('jabatan_role.strategy_roles.global', []))
            ->isNotEmpty();

        if ($contexts->isEmpty()) {
            return new OrganizationContext(
                jabatanId: null,
                prodiId: null,
                fakultasId: null,
                roles: $roleNames,
                isGlobalScope: $isGlobal,
            );
        }

        $active = $contexts->count() === 1
            ? $contexts->first()
            : $contexts->firstWhere('id', $this->session->get(self::SESSION_KEY));

        if ($active === null) {
            // Belum pilih konteks meski punya >1 jabatan aktif. Middleware
            // EnsureOrganizationContext seharusnya sudah mencegah state ini
            // tercapai, tapi kita tetap fail-safe ke "tanpa organisasi"
            // daripada salah menampilkan data organisasi manapun.
            return new OrganizationContext(
                jabatanId: null,
                prodiId: null,
                fakultasId: null,
                roles: $roleNames,
                isGlobalScope: $isGlobal,
            );
        }

        return new OrganizationContext(
            jabatanId: $active->id,
            prodiId: $active->prodi_id,
            fakultasId: $active->fakultas_id ?? $active->prodi?->fakultas_id,
            roles: $roleNames,
            isGlobalScope: $isGlobal,
        );
    }
}
