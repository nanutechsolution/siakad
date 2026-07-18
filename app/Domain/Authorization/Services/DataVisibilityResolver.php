<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Services;

use App\Domain\Authorization\Contracts\HasScopeStrategy;
use App\Domain\Authorization\Enums\ScopeStrategy;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Satu-satunya tempat aturan "data apa yang boleh dilihat user ini" hidup.
 * Dipanggil oleh trait VisibleToUser (query scope) dan oleh Policy (lewat
 * isAccessible()) untuk single-record authorization — sehingga aturan list
 * dan aturan single-record TIDAK PERNAH bisa berbeda/divergen.
 */
final class DataVisibilityResolver
{
    public function __construct(
        private readonly OrganizationResolver $organizationResolver,
    ) {}

    /**
     * @param class-string<Model&HasScopeStrategy> $modelClass
     */
    public function apply(Builder $query, User $user, string $modelClass): Builder
    {

        $this->assertImplementsContract($modelClass);

        $strategy = $this->resolveApplicableStrategy($user, $modelClass);

        if ($strategy === null) {
            // Tidak ada role user yang cocok dengan strategy manapun yang
            // didukung model ini -> query harus kosong, bukan "semua data".
            return $query->whereRaw('1 = 0');
        }

        return match ($strategy) {
            ScopeStrategy::GLOBAL, ScopeStrategy::MODULE_ONLY => $query,
            ScopeStrategy::FAKULTAS => $this->applyColumnScope(
                $query,
                $modelClass::getFakultasScopeColumn(),
                $this->organizationResolver->accessibleFakultasIds($user),
            ),
            ScopeStrategy::PRODI => $this->applyColumnScope(
                $query,
                $modelClass::getProdiScopeColumn(),
                $this->organizationResolver->accessibleProdiIds($user),
            ),
            ScopeStrategy::OWNERSHIP_MAHASISWA,
            ScopeStrategy::OWNERSHIP_DOSEN,
            ScopeStrategy::DOSEN_WALI => $modelClass::applyOwnershipScope($query, $user, $strategy),
        };
    }

    /**
     * Cek apakah SATU record spesifik boleh diakses user — dipakai Policy
     * (view/update/delete/approve/dst). Menjalankan ulang query yang sama
     * dengan apply(), dipersempit ke primary key record tersebut, supaya
     * aturan single-record selalu konsisten dengan aturan list.
     *
     * @param Model&HasScopeStrategy $record
     */
    public function isAccessible(User $user, Model $record): bool
    {
        $modelClass = $record::class;
        $this->assertImplementsContract($modelClass);

        return $this->apply($modelClass::query(), $user, $modelClass)
            ->whereKey($record->getKey())
            ->exists();
    }

    /**
     * Pilih strategy pertama (paling luas) dalam urutan yang dideklarasikan
     * model, yang role-nya dimiliki user. Ini menangani kasus user dengan
     * >1 role (mis. Kaprodi sekaligus Dosen): role terluas yang menang,
     * selama model memang mendukung strategy tersebut.
     *
     * @param class-string<Model&HasScopeStrategy> $modelClass
     */
    private function resolveApplicableStrategy(User $user, string $modelClass): ?ScopeStrategy
    {
        foreach ($modelClass::getSupportedScopeStrategies() as $strategy) {
            $roles = config("jabatan_role.strategy_roles.{$strategy->value}", []);
          
            if ($roles !== [] && $user->hasAnyRole($roles)) {
                return $strategy;
            }
        }
        return null;
    }
    /**
     * @param array<int, int> $accessibleIds
     */
    private function applyColumnScope(Builder $query, ?string $column, array $accessibleIds): Builder
    {
        if ($column === null) {
            throw new RuntimeException(
                'Model mendeklarasikan strategy FAKULTAS/PRODI tapi tidak menyediakan nama kolom scope-nya.'
            );
        }

        if ($accessibleIds === []) {
            return $query->whereRaw('1 = 0');
        }

        if (Str::contains($column, '.')) {
            // Dukung relasi berlapis, mis. 'mahasiswa.prodi.fakultas_id' ->
            // relasi 'mahasiswa.prodi' (dot-notation whereHas bawaan Eloquent
            // untuk nested relationship existence), kolom 'fakultas_id' pada
            // relasi TERAKHIR dalam chain tersebut.
            $lastDotPosition = strrpos($column, '.');
            $relationChain = substr($column, 0, $lastDotPosition);
            $relatedColumn = substr($column, $lastDotPosition + 1);

            return $query->whereHas($relationChain, function (Builder $relatedQuery) use ($relatedColumn, $accessibleIds) {
                $relatedQuery->whereIn($relatedColumn, $accessibleIds);
            });
        }

        return $query->whereIn($column, $accessibleIds);
    }

    /**
     * @param class-string $modelClass
     */
    private function assertImplementsContract(string $modelClass): void
    {
        if (!is_subclass_of($modelClass, HasScopeStrategy::class)) {
            throw new RuntimeException(
                "{$modelClass} harus implement " . HasScopeStrategy::class . ' untuk bisa dipakai dengan visibleTo().'
            );
        }
    }
}
