<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = env('ADMIN_API_KEY');
        $provided = $request->header('X-ADMIN-KEY');

        if (!$expected || $provided !== $expected) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
