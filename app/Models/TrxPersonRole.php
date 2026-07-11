<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class TrxPersonRole extends Model
{
    use LogsActivity;

    protected $table = 'trx_person_role';

    protected $fillable = [
        'person_id',
        'role_id',
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
            ->useLogName('riwayat_person_role');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    public function roleBisnis(): BelongsTo
    {
        return $this->belongsTo(RefPersonRole::class, 'role_id');
    }
}
