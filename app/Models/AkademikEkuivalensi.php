<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AkademikEkuivalensi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'akademik_ekuivalensi';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sks_diakui' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the study program associated with this equivalence rule.
     */
    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    /**
     * Get the origin course.
     */
    public function mkAsal(): BelongsTo
    {
        return $this->belongsTo(MasterMataKuliah::class, 'mk_asal_id');
    }

    /**
     * Get the destination course.
     */
    public function mkTujuan(): BelongsTo
    {
        return $this->belongsTo(MasterMataKuliah::class, 'mk_tujuan_id');
    }

    /**
     * Get the user who created this equivalence rule.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}