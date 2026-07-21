<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmDokumenApproval extends Model
{
    protected $fillable = [
        'dokumen_id',
        'person_id',
        'peran',
        'status',
        'catatan',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(LpmDokumen::class, 'dokumen_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }
}
