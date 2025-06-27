<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TechnicianMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika pengguna tidak login atau bukan teknisi, redirect ke dashboard dengan pesan error
        if (!Auth::check() || !Auth::user()->isTechnician()) {
            return redirect()->route('filament.admin.pages.dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }
        
        return $next($request);
    }
} 