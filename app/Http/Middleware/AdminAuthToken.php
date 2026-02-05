<?php

namespace App\Http\Middleware;

use App\Models\Central\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized. No token provided.'], 401);
        }

        $admin = Admin::where('api_token', hash('sha256', $token))->first();

        if (!$admin) {
            return response()->json(['message' => 'Unauthorized. Invalid token.'], 401);
        }

        if ($admin->status !== 'active') {
            return response()->json(['message' => 'Unauthorized. Account is not active.'], 401);
        }

        // Set the authenticated admin on the request
        $request->merge(['auth_admin' => $admin]);
        $request->setUserResolver(function () use ($admin) {
            return $admin;
        });

        return $next($request);
    }
}

