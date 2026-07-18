<?php

namespace App\Models\Concerns;

use App\Models\UserProdiScope;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tambahkan `use HasProdiScopes;` ke dalam class App\Models\User yang sudah ada.
 * Tidak menimpa file User.php Anda — cukup tambahkan trait ini secara manual.
 */
trait HasProdiScopes
{
    public function prodiScopes(): HasMany
    {
        return $this->hasMany(UserProdiScope::class, 'user_id');
    }
}