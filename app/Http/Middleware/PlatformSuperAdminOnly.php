<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlatformSuperAdminOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isPlatformSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Super Admin platform only.'], 403);
            }

            return redirect()->route('dashboard.app')->with('error', 'Akses khusus Platform Super Admin.');
        }

        return $next($request);
    }
}
