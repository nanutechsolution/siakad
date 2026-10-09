<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

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
 */
class OidcAwareLoginResponse
{
    /** Masa berlaku return-to agar redirect lama tidak mengejutkan user nanti. */
    private const RETURN_TO_TTL = 600;

    public function toResponse($request): RedirectResponse
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
