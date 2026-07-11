<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'username', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids, SoftDeletes, HasRoles;
    protected $keyType = 'string';
    public $incrementing = false;
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the person data associated with the user.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $id = $panel->getId();

        // 1. Panel Mahasiswa
        if ($id === 'mahasiswa') {
            return \App\Models\Mahasiswa::where('person_id', $this->person_id)->exists();
        }

        // 2. Panel Dosen
        if ($id === 'dosen') {
            return \App\Models\TrxDosen::where('person_id', $this->person_id)->exists();
        }

        // 3. Panel Admin (Gunakan hasRole dari Spatie)
        if ($id === 'admin') {
            return $this->hasRole(['admin', 'super_admin']);
        }

        // 4. Default: Tolak akses jika tidak cocok dengan panel manapun
        return false;
    }
}
