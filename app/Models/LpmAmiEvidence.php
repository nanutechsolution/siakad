<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmAmiEvidence extends Model
{
    protected $table = 'lpm_ami_evidences';

    protected $fillable = [
        'checklist_jawaban_id',
        'finding_id',
        'file_path',
        'keterangan',
        'uploaded_by_person_id',
    ];

    public function checklistJawaban(): BelongsTo
    {
        return $this->belongsTo(LpmAmiChecklistJawaban::class, 'checklist_jawaban_id');
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(LpmAmiFinding::class, 'finding_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'uploaded_by_person_id');
    }
}
