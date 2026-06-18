<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDriver
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== 'driver') {
            abort(403, 'Driver access only.');
        }

        return $next($request);
    }
}
