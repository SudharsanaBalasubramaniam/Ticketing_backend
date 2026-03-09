<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ThrottleApiRequests
{
    private static $lastRequestTime = 0;
    private const DELAY_MS = 30;

    public function handle(Request $request, Closure $next)
    {
        $now = microtime(true) * 1000;
        $timeSinceLastRequest = $now - self::$lastRequestTime;

        if ($timeSinceLastRequest < self::DELAY_MS) {
            usleep((self::DELAY_MS - $timeSinceLastRequest) * 1000);
        }

        self::$lastRequestTime = microtime(true) * 1000;

        return $next($request);
    }
}
