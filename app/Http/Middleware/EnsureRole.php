<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        if (! $request->user()->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['username' => 'Akun Anda tidak aktif. Hubungi Pemilik.']);
        }

        if ($roles && ! in_array($request->user()->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}
