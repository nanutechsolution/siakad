<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmBuktiPelaksanaan extends Model
{
    protected $fillable = [
        'iku_target_id',
        'finding_id',
        'unit_kerja_id',
        'judul',
        'file_path',
        'keterangan',
        'uploaded_by_person_id',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function ikuTarget(): BelongsTo
    {
        return $this->belongsTo(LpmIkuTarget::class, 'iku_target_id');
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(LpmAmiFinding::class, 'finding_id');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(LpmUnitKerja::class, 'unit_kerja_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'uploaded_by_person_id');
    }
}
