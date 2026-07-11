<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HR\JenjangGelar;
use App\Enums\HR\PosisiGelar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class RefGelar extends Model
{
    use LogsActivity;

    protected $table = 'ref_gelar';

    protected $fillable = [
        'kode',
        'nama',
        'posisi',
        'jenjang',
    ];

    protected $casts = [
        'posisi' => PosisiGelar::class,
        'jenjang' => JenjangGelar::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('master_gelar');
    }

    public function pemegangGelar(): HasMany
    {
        return $this->hasMany(TrxPersonGelar::class, 'gelar_id');
    }
}