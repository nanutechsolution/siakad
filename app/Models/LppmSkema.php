<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LppmSkema extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lppm_skemas';

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
            'maksimal_dana' => 'decimal:2',
            'tgl_mulai_daftar' => 'date',
            'tgl_tutup_daftar' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the academic year associated with this scheme.
     */
    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }

    /**
     * Get the type reference for this scheme.
     */
    public function jenisSkema(): BelongsTo
    {
        return $this->belongsTo(LppmRefJenisSkema::class, 'jenis_skema_id');
    }

    /**
     * Get the proposals submitted under this scheme.
     */
    public function usulans(): HasMany
    {
        return $this->hasMany(LppmUsulan::class, 'skema_id');
    }
}
