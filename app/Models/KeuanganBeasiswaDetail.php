<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Keuangan\TipeDiskonBeasiswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class KeuanganBeasiswaDetail extends Model
{
    use LogsActivity;

    protected $table = 'keuangan_beasiswa_details';

    protected $fillable = [
        'beasiswa_id',
        'komponen_biaya_id',
        'tipe_diskon',
        'nilai_diskon',
    ];

    protected $casts = [
        'tipe_diskon' => TipeDiskonBeasiswa::class,
        'nilai_diskon' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('beasiswa_detail')
            ->setDescriptionForEvent(function (string $eventName) {
                // Custom log message untuk audit finansial yang readable
                if ($eventName === 'updated') {
                    $dirty = $this->getDirty();
                    if (array_key_exists('nilai_diskon', $dirty) || array_key_exists('tipe_diskon', $dirty)) {
                        $komponen = $this->komponenBiaya->nama_komponen ?? 'Komponen';
                        $beasiswa = $this->beasiswa->nama_beasiswa ?? 'Beasiswa';
                        return "Mengubah nilai/tipe diskon untuk komponen {$komponen} pada {$beasiswa}";
                    }
                }
                return "Telah {$eventName} detail beasiswa";
            });
    }

    public function beasiswa(): BelongsTo
    {
        return $this->belongsTo(KeuanganMasterBeasiswa::class, 'beasiswa_id');
    }

    public function komponenBiaya(): BelongsTo
    {
        // Asumsi: Model KeuanganKomponenBiaya berada di namespace standar
        return $this->belongsTo(\App\Models\KeuanganKomponenBiaya::class, 'komponen_biaya_id');
    }
}