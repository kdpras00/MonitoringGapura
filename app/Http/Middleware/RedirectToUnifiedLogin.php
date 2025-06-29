<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

class RedirectToUnifiedLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika request adalah untuk halaman login Filament (admin atau teknisi)
        // dan bukan untuk halaman login unified, redirect ke login unified
        if (
            $request->is('admin/login') || 
            $request->is('technician/login')
        ) {
            // Redirect ke halaman login unified
            return redirect()->route('unified.login');
        }

        return $next($request);
    }
}
