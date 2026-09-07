<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalGeneratorResult extends Model
{
    protected $table = 'jadwal_generator_results';

    // Mengizinkan insert massal ke semua kolom kecuali ID
    protected $guarded = ['id'];

    // Konversi tipe data otomatis saat dibaca/disimpan ke database
    protected $casts = [
        'dosen_pengampu_ids' => 'array',
        'is_success' => 'boolean',
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(JadwalGeneratorBatch::class, 'batch_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MasterMataKuliah::class, 'mata_kuliah_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function ruang(): BelongsTo
    {
        return $this->belongsTo(RefRuang::class, 'ruang_id');
    }
}
