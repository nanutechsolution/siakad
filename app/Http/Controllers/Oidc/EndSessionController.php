<?php

declare(strict_types=1);

namespace App\Http\Controllers\Oidc;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Client;

class EndSessionController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $postLogoutUri = $request->input('post_logout_redirect_uri');
        $clientId = $request->input('client_id');

        // Only allow redirects explicitly registered by the identified client.
        if (is_string($postLogoutUri) && is_string($clientId) && $clientId !== '') {
            $client = Client::query()->whereKey($clientId)->where('revoked', false)->first();
            $allowed = $client?->post_logout_redirect_uris ?? [];

            if (is_string($allowed)) {
                $allowed = json_decode($allowed, true) ?: [];
            }

            if (is_array($allowed) && in_array($postLogoutUri, $allowed, true)) {
                auth()->guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->away($postLogoutUri);
            }

            abort(400, 'post_logout_redirect_uri is not registered for this client.');
        }

        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
