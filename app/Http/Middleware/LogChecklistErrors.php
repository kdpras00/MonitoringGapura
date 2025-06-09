<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class LogChecklistErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (\Exception $e) {
            // Jika error berkaitan dengan foreach dan string
            if (strpos($e->getMessage(), 'foreach()') !== false && 
                strpos($e->getMessage(), 'string given') !== false) {
                
                // Log detail error untuk tracking
                Log::error('Checklist foreach error', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => auth()->id(),
                ]);
                
                // Tampilkan halaman error khusus untuk masalah checklist
                return response()->view('errors.checklist', [
                    'error' => $e->getMessage(),
                    'url' => $request->fullUrl()
                ], 500);
            }
            
            // Jika bukan error foreach, teruskan ke handler exception berikutnya
            throw $e;
        }
    }
}
