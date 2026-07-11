<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HR\JenisJabatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class RefJabatan extends Model
{
    use LogsActivity;

    protected $table = 'ref_jabatan';

    protected $fillable = [
        'kode_jabatan',
        'nama_jabatan',
        'jenis',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'jenis' => JenisJabatan::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('master_jabatan');
    }

    public function penugasan(): HasMany
    {
        return $this->hasMany(TrxPersonJabatan::class, 'jabatan_id');
    }
}