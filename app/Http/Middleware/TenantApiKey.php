<?php

namespace App\Http\Middleware;

use App\Models\Central\Shop;
use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');

        if (!$apiKey) {
            return response()->json(['message' => 'Missing API key'], 401);
        }

        $shop = Shop::where('api_key', $apiKey)->where('status', 'active')->first();

        if (!$shop) {
            return response()->json(['message' => 'Invalid API key'], 401);
        }

        app(TenantManager::class)->setShop($shop);
        $request->attributes->set('shop', $shop);

        return $next($request);
    }
}
