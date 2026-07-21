<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmUnitPic extends Model
{
    protected $fillable = [
        'unit_kerja_id',
        'person_id',
        'peran',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(LpmUnitKerja::class, 'unit_kerja_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    public function isAktif(): bool
    {
        return $this->tanggal_selesai === null || $this->tanggal_selesai->isFuture();
    }
}
