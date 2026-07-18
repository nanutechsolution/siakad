<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Concerns\HasCurrentOrganization;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasUuids, SoftDeletes, HasRoles, HasApiTokens, HasDatabaseNotifications, HasCurrentOrganization;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'person_id',
        'password',
        'username',
        'is_active'
    ];
    public function scopeVisibleTo(Builder $query, User $actor): Builder
    {
        if ($actor->hasRole('super_admin')) {
            return $query;
        }

        $resolver = app(
            \App\Domain\Authorization\Services\OrganizationResolver::class
        );

        $prodiIds = $resolver->accessibleProdiIds($actor);


        if ($prodiIds === []) {
            return $query->whereRaw('1 = 0');
        }


        return $query->whereHas('person', function ($person) use ($prodiIds) {

            $person->whereHas('mahasiswa', function ($mhs) use ($prodiIds) {
                $mhs->whereIn('prodi_id', $prodiIds);
            })
                ->orWhereHas('dosen', function ($dosen) use ($prodiIds) {
                    $dosen->whereIn('prodi_id', $prodiIds);
                });
        });
    }
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
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }
    /**
     * Get the person data associated with the user.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }
    public function mahasiswa(): HasOne
    {
        return $this->hasOne(
            Mahasiswa::class,
            'person_id',
            'person_id'
        );
    }

    public function dosen(): HasOne
    {
        return $this->hasOne(
            TrxDosen::class,
            'person_id',
            'person_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Access
    |--------------------------------------------------------------------------
    */


    public function isDosen(): bool
    {
        return $this->dosen()->exists();
    }


    public function isMahasiswa(): bool
    {
        return $this->mahasiswa()->exists();
    }
    public function canAccessAdmin(): bool
    {
        return $this->hasAnyRole(
            collect(config('jabatan_role.strategy_roles'))
                ->flatten()
                ->push('admin')
                ->unique()
                ->all()
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Filament Panel Access
    |--------------------------------------------------------------------------
    */

    public function canAccessPanel(Panel $panel): bool
    {

        return match ($panel->getId()) {


            'mahasiswa' =>
            $this->isMahasiswa(),


            'dosen' =>
            $this->isDosen(),


            'admin' =>
            $this->canAccessAdmin(),


            default => false,
        };
    }
}
