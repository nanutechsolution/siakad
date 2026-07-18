<?php

declare(strict_types=1);

namespace App\Domain\Authorization\Contracts;

use App\Domain\Authorization\Enums\ScopeStrategy;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Kontrak wajib untuk setiap Model yang datanya perlu difilter otomatis
 * lewat `Model::visibleTo($user)` (trait VisibleToUser) dan
 * DataVisibilityResolver.
 *
 * Setiap model mendeklarasikan SEMUA strategi yang relevan untuknya,
 * diurutkan dari yang paling luas ke paling sempit. DataVisibilityResolver
 * akan memilih strategi pertama dalam urutan ini yang role user-nya cocok
 * (lihat config/jabatan_role.php -> strategy_roles). Urutan ini penting
 * untuk kasus user dengan multi-role (mis. Kaprodi sekaligus Dosen): role
 * terluas yang menang.
 */
interface HasScopeStrategy
{
    /**
     * @return ScopeStrategy[] Urutan dari paling luas ke paling sempit.
     */
    public static function getSupportedScopeStrategies(): array;

    /**
     * Nama kolom (atau dot-path relasi, mis. 'prodi.fakultas_id') yang dipakai
     * untuk strategi FAKULTAS. Return null jika model ini tidak mendukung
     * strategi FAKULTAS.
     */
    public static function getFakultasScopeColumn(): ?string;

    /**
     * Nama kolom (atau dot-path relasi) yang dipakai untuk strategi PRODI.
     * Return null jika model ini tidak mendukung strategi PRODI.
     */
    public static function getProdiScopeColumn(): ?string;

    /**
     * Dipanggil untuk strategi OWNERSHIP_MAHASISWA / OWNERSHIP_DOSEN / DOSEN_WALI.
     * Model mengimplementasikan sendiri constraint query-nya (biasanya lewat
     * whereHas ke pivot/relasi terkait). Jika model tidak mendukung strategi
     * ownership apa pun, method ini boleh melempar LogicException.
     */
    public static function applyOwnershipScope(Builder $query, User $user, ScopeStrategy $strategy): Builder;
}
