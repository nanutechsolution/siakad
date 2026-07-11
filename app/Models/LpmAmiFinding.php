<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAmiFinding extends Model
{
    use HasFactory;

    protected $table = 'lpm_ami_findings';
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'deadline_perbaikan' => 'date',
            'is_closed' => 'boolean',
        ];
    }

    public function periode(): BelongsTo
    {
        return $this->belongsTo(LpmAmiPeriode::class, 'periode_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    public function standar(): BelongsTo
    {
        return $this->belongsTo(LpmStandar::class, 'standar_id');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(LpmAmiDiscussion::class, 'finding_id');
    }
}