<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LppmUsulanAnggota extends Model
{
    use HasFactory;

    protected $table = 'lppm_usulan_anggotas';
    protected $guarded = ['id'];

    public function usulan(): BelongsTo
    {
        return $this->belongsTo(LppmUsulan::class, 'usulan_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(RefPerson::class, 'person_id');
    }
}