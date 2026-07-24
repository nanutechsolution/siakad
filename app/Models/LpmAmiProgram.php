<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class LpmAmiProgram extends Model
{
    protected $table = 'lpm_ami_programs';

    protected $fillable = [
        'periode_id',
        'unit_kerja_id',
        'tanggal_pelaksanaan',
        'status',
    ];

    protected $casts = [
        'tanggal_pelaksanaan' => 'date',
    ];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(LpmAmiPeriode::class, 'periode_id');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(LpmUnitKerja::class, 'unit_kerja_id');
    }

    public function programAuditors(): HasMany
    {
        return $this->hasMany(LpmAmiProgramAuditor::class, 'program_id');
    }

    public function auditors(): HasManyThrough
    {
        return $this->hasManyThrough(
            LpmAuditor::class,
            LpmAmiProgramAuditor::class,
            'program_id',
            'id',
            'id',
            'auditor_id'
        );
    }

    public function checklistJawabans(): HasMany
    {
        return $this->hasMany(LpmAmiChecklistJawaban::class, 'program_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(LpmAmiFinding::class, 'program_id');
    }
}
