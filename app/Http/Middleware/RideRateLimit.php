<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RideRateLimit
{
    /**
     * Limit ride requests to 5 per 2 minutes per user.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key   = 'ride_request_' . $request->user()?->id;
        $count = (int) Cache::get($key, 0);

        if ($count >= 5) {
            return response()->json(
                ['error' => 'Too many requests. Please wait a moment before trying again.'],
                429
            );
        }

        Cache::put($key, $count + 1, now()->addMinutes(2));

        return $next($request);
    }
}
