<?php

namespace App\Models;

use App\Enums\Keuangan\KategoriBeasiswa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class KeuanganMasterBeasiswa extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'keuangan_master_beasiswas';

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
    protected $casts = [
        'kategori' => KategoriBeasiswa::class,
        'is_active' => 'boolean',
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('master_beasiswa');
    }
    /**
     * Get the component discount details for this scholarship.
     */
    public function details(): HasMany
    {
        return $this->hasMany(KeuanganBeasiswaDetail::class, 'beasiswa_id');
    }

    /**
     * Get the student enrollments for this scholarship.
     */
    public function mahasiswaBeasiswas(): HasMany
    {
        return $this->hasMany(KeuanganMahasiswaBeasiswa::class, 'beasiswa_id');
    }
}
