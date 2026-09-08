<?php

namespace App\Models;

use App\Domain\Authorization\Contracts\HasScopeStrategy;
use App\Domain\Authorization\Enums\ScopeStrategy;
use App\Models\Concerns\VisibleToUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Master Ketersediaan Dosen.
 *
 * Menyimpan interval waktu (hari + jam_mulai + jam_selesai) di mana seorang
 * dosen tersedia untuk mengajar. Data ini adalah HARD CONSTRAINT bagi
 * App\Services\Scheduling\JadwalGeneratorEngine — kolom `dosen_id`, `hari`,
 * `jam_mulai`, `jam_selesai` TIDAK BOLEH diubah namanya tanpa menyesuaikan
 * engine tersebut.
 */
class DosenKetersediaan extends Model implements HasScopeStrategy
{
    use VisibleToUser;

    protected $table = 'dosen_ketersediaans';

    protected $fillable = [
        'dosen_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    /**
     * Urutan hari yang valid, dipakai untuk validasi & sorting logis
     * (bukan alfabetis) di seluruh aplikasi.
     */
    public const HARI_URUTAN = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
    ];

    protected $casts = [
        // Disimpan sebagai string format H:i:s (kolom TIME di MySQL).
        // Tidak di-cast ke Carbon/date agar perbandingan string "HH:MM:SS"
        // pada query overlap tetap valid secara leksikografis dan konsisten
        // dengan cara JadwalGeneratorEngine membaca data ini saat ini.
    ];

    /**
     * Relasi ke dosen pemilik jadwal ketersediaan ini.
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(TrxDosen::class, 'dosen_id');
    }

    /**
     * Cek apakah interval [jamMulai, jamSelesai) pada hari tertentu untuk
     * seorang dosen bertabrakan dengan data ketersediaan lain yang sudah ada.
     *
     * Rumus overlap matematis:
     *   existing.jam_mulai < new.jam_selesai
     *   AND existing.jam_selesai > new.jam_mulai
     *
     * Rumus yang sama juga otomatis menolak duplikat interval identik,
     * sehingga tidak diperlukan unique rule terpisah.
     */
    public static function findBentrok(
        string $dosenId,
        string $hari,
        string $jamMulai,
        string $jamSelesai,
        ?string $kecualiId = null,
    ): ?self {
        return static::query()
            ->where('dosen_id', $dosenId)
            ->where('hari', $hari)
            ->when($kecualiId, fn(Builder $query) => $query->whereKeyNot($kecualiId))
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai)
            ->first();
    }

    // --- Authorization scope (HasScopeStrategy) --------------------------
    //
    // ASUMSI YANG PERLU DIVERIFIKASI: tabel ini tidak punya kolom prodi_id /
    // fakultas_id sendiri, jadi scope PRODI/FAKULTAS di sini dinavigasi lewat
    // relasi `dosen`, mengikuti pola dot-notation yang sama seperti yang
    // dipakai TrxDosen::getFakultasScopeColumn() ('prodi.fakultas_id').
    // Saya BELUM melihat source VisibleToUser, jadi tolong konfirmasi bahwa
    // trait ini memang mendukung dot-notation dua tingkat relasi
    // ('dosen.prodi.fakultas_id') sebelum dipakai di production.

    public static function getSupportedScopeStrategies(): array
    {
        return [ScopeStrategy::GLOBAL, ScopeStrategy::FAKULTAS, ScopeStrategy::PRODI];
    }

    public static function getFakultasScopeColumn(): ?string
    {
        return 'dosen.prodi.fakultas_id';
    }

    public static function getProdiScopeColumn(): ?string
    {
        return 'dosen.prodi_id';
    }

    public static function applyOwnershipScope(Builder $query, User $user, ScopeStrategy $strategy): Builder
    {
        throw new \LogicException('DosenKetersediaan tidak mendukung strategy ownership.');
    }
}
