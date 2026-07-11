<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class RefPersonRole extends Model
{
    use LogsActivity;

    protected $table = 'ref_person_role';

    protected $fillable = [
        'kode_role',
        'nama_role',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('master_person_role');
    }

    public function penugasan(): HasMany
    {
        return $this->hasMany(TrxPersonRole::class, 'role_id');
    }

    public function gelars(): HasMany
    {
        return $this->hasMany(TrxPersonGelar::class, 'person_id')
            ->orderBy('urutan');
    }
}
