<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAmiChecklist extends Model
{
    protected $table = 'lpm_ami_checklists';

    protected $fillable = [
        'standar_id',
        'kriteria',
        'urutan',
    ];

    public function standar(): BelongsTo
    {
        return $this->belongsTo(LpmStandar::class, 'standar_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LpmAmiChecklistItem::class, 'checklist_id')->orderBy('urutan');
    }
}
