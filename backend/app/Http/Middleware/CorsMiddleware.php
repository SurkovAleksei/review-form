<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = config('cors.allowed_origins', []);

        $origin = $request->header('Origin');

        if (in_array($origin, $allowedOrigins) || app()->environment('local')) {
            return $next($request)
                ->header('Access-Control-Allow-Origin', $origin ?? '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        return response()->json([
            'success' => false,
            'message' => 'CORS политика: доступ запрещен',
            'errors' => ['Запрос с этого домена не разрешен']
        ], 403);
    }
}
