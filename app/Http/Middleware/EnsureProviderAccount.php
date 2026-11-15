<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role === 'provider' && $request->user()->providerProfile, 403);

        return $next($request);
    }
}
