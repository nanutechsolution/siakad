<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Exception;
use Illuminate\Validation\ValidationException;

class DosenPengampu extends Model
{
    protected $table = 'dosen_pengampus';
    protected $guarded = ['id'];
   protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->is_koordinator) {
                // Cek apakah di kelas dan MK yang sama, sudah ada dosen lain yang jadi koordinator
                $existingKoordinator = self::where('kelas_id', $model->kelas_id)
                    ->where('mata_kuliah_id', $model->mata_kuliah_id)
                    ->where('id', '!=', $model->id) // Abaikan diri sendiri jika sedang proses update
                    ->where('is_koordinator', true)
                    ->exists();

                if ($existingKoordinator) {
                    // Gunakan ValidationException agar ditangkap oleh UI Filament
                    throw ValidationException::withMessages([
                        'is_koordinator' => 'Sudah ada Dosen Koordinator untuk Mata Kuliah ini di kelas tersebut. Harap matikan toggle koordinator.',
                    ]);
                }
            }
        });
    }
    public function tahunAkademik(): BelongsTo { return $this->belongsTo(RefTahunAkademik::class, 'tahun_akademik_id'); }
    public function mataKuliah(): BelongsTo { return $this->belongsTo(MasterMataKuliah::class, 'mata_kuliah_id'); }
    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class, 'kelas_id'); }
    public function dosen(): BelongsTo { return $this->belongsTo(TrxDosen::class, 'dosen_id'); }
    public function ruang(): BelongsTo { return $this->belongsTo(RefRuang::class, 'ruang_id'); }
}