<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAmiFinding extends Model
{
    protected $table = 'lpm_ami_findings';

    protected $fillable = [
        'periode_id',
        'program_id',
        'prodi_id',
        'jenis_temuan',
        'standar_id',
        'auditor_name',
        'auditor_id',
        'klasifikasi',
        'deskripsi_temuan',
        'rekomendasi',
        'akar_masalah',
        'rencana_tindak_lanjut',
        'preventive_action',
        'deadline_perbaikan',
        'is_closed',
        'status_workflow',
    ];

    protected $casts = [
        'deadline_perbaikan' => 'date',
        'is_closed' => 'boolean',
    ];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(LpmAmiPeriode::class, 'periode_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(LpmAmiProgram::class, 'program_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    public function standar(): BelongsTo
    {
        return $this->belongsTo(LpmStandar::class, 'standar_id');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(LpmAuditor::class, 'auditor_id');
    }

    public function checklistJawaban(): HasMany
    {
        return $this->hasMany(LpmAmiChecklistJawaban::class, 'finding_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(LpmAmiEvidence::class, 'finding_id');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(LpmAmiDiscussion::class, 'finding_id');
    }
}