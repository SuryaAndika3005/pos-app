<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Batasi akses ke halaman yang hanya boleh dibuka oleh role 'admin'
     * (mis. Gudang dan Laporan). Kasir biasa akan ditolak dengan pesan jelas
     * dan dikembalikan ke halaman Kasir POS.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            return redirect()
                ->route('pos.index')
                ->with('status', 'Halaman ini hanya bisa diakses oleh Admin.');
        }

        return $next($request);
    }
}
