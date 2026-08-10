<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class JadwalKomponenNilai extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }
    protected $table = 'jadwal_komponen_nilai';

    protected $fillable = ['jadwal_kuliah_id', 'komponen_id', 'bobot_persen'];

    protected $casts = ['bobot_persen' => 'decimal:2'];

    public function jadwalKuliah(): BelongsTo
    {
        return $this->belongsTo(JadwalKuliah::class, 'jadwal_kuliah_id');
    }

    public function komponen(): BelongsTo
    {
        return $this->belongsTo(RefKomponenNilai::class, 'komponen_id');
    }

    public function masterKomponen(): BelongsTo
    {
        return $this->belongsTo(RefKomponenNilai::class, 'komponen_id');
    }
}
