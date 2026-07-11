<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class TrxPersonJabatan extends Model
{
    use LogsActivity;

    protected $table = 'trx_person_jabatan';

    protected $fillable = [
        'person_id',
        'jabatan_id',
        'fakultas_id',
        'prodi_id',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('riwayat_jabatan');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(RefJabatan::class, 'jabatan_id');
    }

    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(RefFakultas::class, 'fakultas_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }
}