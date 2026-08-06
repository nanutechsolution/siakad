<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Jika user login dan flag must_change_password = true
        if ($user && $user->must_change_password) {

            // Izinkan akses jika mereka sedang berada di halaman ganti password, 
            // halaman logout, atau memproses form ganti password
            if ($request->routeIs('password.force-change', 'password.force-change.store', 'logout', 'filament.admin.auth.logout')) {
                return $next($request);
            }

            // Redirect paksa ke halaman ganti password
            return redirect()->route('password.force-change')
                ->with('warning', 'Sistem mendeteksi password Anda direset oleh Admin. Demi keamanan, Anda wajib membuat password baru sekarang.');
        }

        return $next($request);
    }
}
