<?php

namespace App\Services\Pdf;

use App\Settings\KampusSettings;
use Illuminate\Support\Facades\Storage;

class KopSuratResolver
{
    public function resolve(): array
    {
        $settings = app(KampusSettings::class);

        return [
            'nama' => $settings->nama,
            'namaSingkat' => $settings->nama_singkat,
            'alamat' => $settings->alamat,
            'telepon' => $settings->telepon,
            'email' => $settings->email,
            'website' => $settings->website,
            'akreditasi' => $settings->akreditasi,
            'logoAbsolutePath' => $this->resolveLogoPath($settings->logo_path),
        ];
    }

    protected function resolveLogoPath(?string $logoPath): ?string
    {
        if (blank($logoPath)) {
            return null;
        }

        if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
            return $logoPath;
        }

        // Asumsi: logo_path disimpan sebagai path relatif di disk 'public'
        // (pola umum Filament FileUpload). Sesuaikan bila disk Anda berbeda.
        if (Storage::disk('public')->exists($logoPath)) {
            return Storage::disk('public')->path($logoPath);
        }

        $publicPath = public_path($logoPath);

        return file_exists($publicPath) ? $publicPath : null;
    }
}
