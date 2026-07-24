<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LpmAmiChecklistJawaban extends Model
{
    protected $table = 'lpm_ami_checklist_jawabans';

    protected $fillable = [
        'program_id',
        'checklist_item_id',
        'jawaban',
        'catatan',
        'finding_id',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(LpmAmiProgram::class, 'program_id');
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(LpmAmiChecklistItem::class, 'checklist_item_id');
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(LpmAmiFinding::class, 'finding_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(LpmAmiEvidence::class, 'checklist_jawaban_id');
    }

    public function memicuTemuan(): bool
    {
        return in_array($this->jawaban, ['TIDAK_SESUAI', 'OBSERVASI'], true);
    }
}
