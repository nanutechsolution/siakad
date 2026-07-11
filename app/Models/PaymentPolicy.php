<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class PaymentPolicy extends Model
{
    use LogsActivity;

    protected $table = 'payment_policies';

    protected $fillable = [
        'tahun_akademik_id',
        'nama',
        'prodi_id',
        'program_kelas_id',
        'angkatan',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Payment Policy has been {$eventName}");
    }

    public function tahunAkademik(): BelongsTo
    {
        return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id');
    }

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(RefProdi::class, 'prodi_id');
    }

    public function programKelas(): BelongsTo
    {
        return $this->belongsTo(RefProgram::class, 'program_kelas_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PaymentPolicyDetail::class, 'payment_policy_id');
    }
}