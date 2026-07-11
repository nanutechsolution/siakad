<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class TrxPersonGelar extends Model
{
    use LogsActivity;

    protected $table = 'trx_person_gelar';

    protected $fillable = [
        'person_id',
        'gelar_id',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('atribusi_gelar');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    public function gelar(): BelongsTo
    {
        return $this->belongsTo(RefGelar::class, 'gelar_id');
    }
}