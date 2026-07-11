<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerkuliahanAbsensi extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'perkuliahan_absensi';

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
            'waktu_check_in' => 'datetime',
            'bukti_validasi' => 'json',
            'is_manual_update' => 'boolean',
        ];
    }

    /**
     * Get the session associated with the attendance.
     */
    public function perkuliahanSesi(): BelongsTo
    {
        return $this->belongsTo(PerkuliahanSesi::class, 'perkuliahan_sesi_id');
    }

    /**
     * Get the KRS detail representing the student enrollment.
     */
    public function krsDetail(): BelongsTo
    {
        return $this->belongsTo(KrsDetail::class, 'krs_detail_id');
    }

    /**
     * Get the user who manually updated the attendance (if applicable).
     */
    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by_user_id');
    }
}
