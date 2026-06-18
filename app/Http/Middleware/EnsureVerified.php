<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_verified) {
            return redirect()->route('verify')
                ->with('warning', 'Please verify your account before continuing.');
        }

        return $next($request);
    }
}
