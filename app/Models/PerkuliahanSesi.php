<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PerkuliahanSesi extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'perkuliahan_sesi';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $fillable = [
        'jadwal_kuliah_id',
        'pertemuan_ke',
        'waktu_mulai_rencana',
        'waktu_mulai_realisasi',
        'waktu_selesai_realisasi',
        'materi_kuliah',
        'catatan_dosen',
        'token_sesi',
        'metode_validasi',
        'status_sesi',
    ];


    protected $keyType = 'string';

    public $incrementing = false;
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'pertemuan_ke' => 'integer',
        'waktu_mulai_rencana' => 'datetime',
        'waktu_mulai_realisasi' => 'datetime',
        'waktu_selesai_realisasi' => 'datetime',
        'status_sesi' => \App\Enums\StatusSesiEnum::class,
    ];
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (empty($model->getKey())) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the class schedule for the session.
     */
    public function jadwalKuliah(): BelongsTo
    {
        return $this->belongsTo(JadwalKuliah::class, 'jadwal_kuliah_id');
    }


    public function absensi(): HasMany
    {
        return $this->hasMany(PerkuliahanAbsensi::class, 'perkuliahan_sesi_id');
    }
}
