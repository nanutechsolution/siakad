<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Authorization\Services\JabatanContextResolver;
use App\Filament\Pages\PilihKonteksKerja;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memaksa user dengan >1 jabatan aktif untuk memilih konteks kerja sebelum
 * mengakses panel manapun. User dengan 0 atau 1 jabatan aktif langsung
 * lewat tanpa gangguan (auto-resolved oleh JabatanContextResolver::current()).
 *
 * Daftarkan di Panel Provider terkait (mis. AdminPanelProvider):
 *     ->middleware([..., EnsureOrganizationContext::class])
 *     ->pages([..., \App\Filament\Pages\PilihKonteksKerja::class])
 */
final class EnsureOrganizationContext
{
    public function __construct(
        private readonly JabatanContextResolver $contextResolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $targetUrl = PilihKonteksKerja::getUrl();

        if ($this->contextResolver->requiresSelection($user) && $request->fullUrl() !== $targetUrl) {
            return redirect($targetUrl);
        }

        return $next($request);
    }
}
