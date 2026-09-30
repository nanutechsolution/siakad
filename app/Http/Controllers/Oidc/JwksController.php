<?php

declare(strict_types=1);

namespace App\Http\Controllers\Oidc;

use App\Services\Oidc\IdTokenSigner;
use Illuminate\Http\JsonResponse;

class JwksController
{
    public function __invoke(IdTokenSigner $signer): JsonResponse
    {
        return response()->json($signer->jwks())
            ->header('Cache-Control', 'public, max-age=300');
    }
}
