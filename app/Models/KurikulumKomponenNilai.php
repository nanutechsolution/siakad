<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KurikulumKomponenNilai extends Model
{
    // Deklarasikan nama tabel sesuai skema database asli Anda
    protected $table = 'kurikulum_komponen_nilai'; //

    protected $fillable = [
        'kurikulum_id',
        'komponen_id',
        'bobot_persen', //
    ];

    /**
     * Relasi balik ke Master Kurikulum
     */
    public function kurikulum(): BelongsTo
    {
        // Sesuaikan nama class model Kurikulum Anda (MasterKurikulum atau Kurikulum)
        return $this->belongsTo(MasterKurikulum::class, 'kurikulum_id');
    }

    /**
     * Relasi ke Master Komponen Nilai
     */
    public function masterKomponen(): BelongsTo
    {
        return $this->belongsTo(RefKomponenNilai::class, 'komponen_id');
    }
}
