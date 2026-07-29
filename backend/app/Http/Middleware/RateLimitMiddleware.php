<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $filePath = storage_path('app/rate_limits.json');
        $limit = config('rate_limit.requests', 5);
        $window = config('rate_limit.window', 3600);

        $data = [];
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true) ?? [];
        }

        $now = time();

        foreach ($data as $key => $item) {
            if ($now - $item['last_request'] > 86400) {
                unset($data[$key]);
            }
        }

        if (isset($data[$ip])) {
            $data[$ip]['requests'] = array_filter(
                $data[$ip]['requests'],
                function ($timestamp) use ($now, $window) {
                    return ($now - $timestamp) < $window;
                }
            );

            if (count($data[$ip]['requests']) >= $limit) {
                $retryAfter = $window - ($now - $data[$ip]['last_request']);

                return response()->json([
                    'success' => false,
                    'message' => 'Слишком много запросов',
                    'errors' => ["Попробуйте через " . ceil($retryAfter / 60) . " минут"],
                    'retry_after' => $retryAfter,
                    'limit' => $limit
                ], 429)
                ->header('X-RateLimit-Limit', $limit)
                ->header('X-RateLimit-Remaining', 0)
                ->header('Retry-After', $retryAfter);
            }
        }

        if (!isset($data[$ip])) {
            $data[$ip] = [
                'requests' => [],
                'last_request' => $now
            ];
        }

        $data[$ip]['requests'][] = $now;
        $data[$ip]['last_request'] = $now;

        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));

        $remaining = $limit - count($data[$ip]['requests']);

        $response = $next($request);
        $response->header('X-RateLimit-Limit', $limit);
        $response->header('X-RateLimit-Remaining', max(0, $remaining));

        return $response;
    }
}
