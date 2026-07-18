<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Domain\Authorization\Services\DataVisibilityResolver;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Tambahkan trait ini ke model manapun yang perlu difilter otomatis sesuai
 * hak akses user (mahasiswa, dosen, kurikulum, KRS, nilai, tagihan, dst).
 * Model wajib juga implement App\Domain\Authorization\Contracts\HasScopeStrategy.
 *
 * Pemakaian di Filament Resource:
 *
 *     public static function getEloquentQuery(): Builder
 *     {
 *         return parent::getEloquentQuery()->visibleTo(auth()->user());
 *     }
 *
 * Atau langsung di kode lain: Mahasiswa::visibleTo($user)->get();
 */
trait VisibleToUser
{
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return app(DataVisibilityResolver::class)->apply($query, $user, static::class);
    }
}
