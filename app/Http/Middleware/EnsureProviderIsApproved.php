<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $provider = $request->user()?->providerProfile;

        abort_unless($provider && $provider->status === 'approved', 403);

        return $next($request);
    }
}
