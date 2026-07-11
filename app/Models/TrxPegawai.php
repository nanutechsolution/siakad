<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HR\JenisPegawai;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class TrxPegawai extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'trx_pegawai';

    protected $fillable = [
        'person_id',
        'nip',
        'jenis_pegawai',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'jenis_pegawai' => JenisPegawai::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('hr_pegawai');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    public function riwayatJabatan(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TrxPersonJabatan::class, 'person_id', 'person_id');
    }

    public function atribusiGelar(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TrxPersonGelar::class, 'person_id', 'person_id')
            ->orderBy('urutan');
    }

    public function riwayatRole(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TrxPersonRole::class, 'person_id', 'person_id');
    }
}
