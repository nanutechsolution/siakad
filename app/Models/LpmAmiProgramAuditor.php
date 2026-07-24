<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmAmiProgramAuditor extends Model
{
    protected $table = 'lpm_ami_program_auditors';

    protected $fillable = [
        'program_id',
        'auditor_id',
        'peran',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(LpmAmiProgram::class, 'program_id');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(LpmAuditor::class, 'auditor_id');
    }
}
