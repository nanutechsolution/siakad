<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAuditor extends Model
{
    protected $fillable = [
        'person_id',
        'no_sertifikat_auditor',
        'kompetensi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }

    public function programAssignments(): HasMany
    {
        return $this->hasMany(LpmAmiProgramAuditor::class, 'auditor_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(LpmAmiFinding::class, 'auditor_id');
    }
}
