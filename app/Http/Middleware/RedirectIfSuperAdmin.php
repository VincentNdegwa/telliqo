<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isSuperAdmin()) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        return $next($request);
    }
}
