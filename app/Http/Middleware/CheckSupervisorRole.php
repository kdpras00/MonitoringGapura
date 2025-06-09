<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSupervisorRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Izinkan jika user adalah admin atau supervisor
        if (auth()->user()->isAdmin() || auth()->user()->isSupervisor()) {
            return $next($request);
        }

        // Redirect jika user tidak memiliki akses
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
} 