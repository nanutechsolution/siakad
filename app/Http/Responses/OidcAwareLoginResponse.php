<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Livewire\Features\SupportRedirects\Redirector;

/**
 * Login response khusus Siakad.
 *
 * Kebanyakan login panel tetap memakai perilaku Filament
 * (`redirect()->intended(...)`). Namun alur authorization-code OIDC tidak
 * boleh bergantung pada url.intended: halaman portal `/` bersifat publik,
 * sehingga navigasi lanjutan ke /admin|/dosen|/mahasiswa menimpa intended URL
 * dan user berakhir di dashboard, sementara authorization request hilang.
 *
 * Karena itu AuthorizeController menyimpan path authorize pada session
 * `oidc.return_to`, dan response ini mengembalikan user persis ke
 * /oauth/authorize?... dengan query string utuh.
 *
 * WAJIB `implements LoginResponse`: `Login::authenticate()` mengembalikan
 * `app(LoginResponse::class)` dengan return type berbasis kontrak
 * (`?Contracts\LoginResponse`). Tanpa implement interface ini, setiap
 * login panel melempar TypeError (500).
 *
 * Return type `RedirectResponse | Redirector` WAJIB union — mengikuti
 * LoginResponse bawaan Filament. Saat request Livewire (form login Filament
 * adalah Livewire), helper `redirect()` di-bind ulang ke
 * Livewire\...\Redirector, sehingga `redirect()->to()`/`intended()`
 * mengembalikan Redirector, bukan RedirectResponse.
 */
class OidcAwareLoginResponse implements LoginResponse
{
    /** Masa berlaku return-to agar redirect lama tidak mengejutkan user nanti. */
    private const RETURN_TO_TTL = 600;

    public function toResponse($request): RedirectResponse|Redirector
    {
        $returnTo = $this->takeReturnTo($request);

        if ($returnTo !== null) {
            return redirect()->to($returnTo);
        }

        return redirect()->intended(Filament::getUrl());
    }

    /** Ambil URL authorize yang tervalidasi, hanya path internal /oauth/authorize. */
    private function takeReturnTo($request): ?string
    {
        $stored = $request->session()->pull('oidc.return_to');

        if (! is_array($stored) || ! isset($stored['url'], $stored['at'])) {
            return null;
        }

        $url = $stored['url'];

        if (! is_string($url) || ! Str::startsWith($url, '/oauth/authorize')) {
            return null;
        }

        if ((int) $stored['at'] < time() - self::RETURN_TO_TTL) {
            return null;
        }

        return $url;
    }
}
