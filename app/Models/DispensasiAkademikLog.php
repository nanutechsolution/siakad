<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class DispensasiAkademikLog extends Model
{
    const UPDATED_AT = null;

    protected $table = 'dispensasi_akademik_logs';

    protected $fillable = [
        'dispensasi_id',
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

    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }
    public function dispensasi(): BelongsTo
    {
        return $this->belongsTo(DispensasiAkademik::class, 'dispensasi_id');
    }

    public function dilakukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }
}
