<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KrsStatusLog extends Model
{
    const UPDATED_AT = null;

    protected $table = 'krs_status_logs';

    protected $fillable = [
        'krs_id',
        'aksi',
        'dilakukan_oleh',
        'before_data',
        'after_data',
        'catatan',
    ];

    protected $casts = [
        'before_data' => 'array',
        'after_data' => 'array',
        'created_at' => 'datetime',
    ];

    public function krs(): BelongsTo
    {
        return $this->belongsTo(Krs::class, 'krs_id');
    }

    public function dilakukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }
}