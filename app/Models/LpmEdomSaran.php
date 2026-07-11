<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpmEdomSaran extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lpm_edom_saran';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * Get the study plan detail (KRS detail) associated with this feedback/suggestion.
     */
    public function krsDetail(): BelongsTo
    {
        return $this->belongsTo(KrsDetail::class, 'krs_detail_id');
    }
}