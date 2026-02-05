<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Central\Shop;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function index()
    {
        return Shop::orderBy('id', 'desc')->get();
    }

    public function store(Request $request, TenantManager $tenantManager)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'db_host' => 'required|string|max:120',
            'db_port' => 'required|integer',
            'db_name' => 'required|string|max:120',
            'db_username' => 'required|string|max:120',
            'db_password' => 'required|string|max:120',
            'db_timezone' => 'nullable|string|max:64',
        ]);

        $shop = Shop::create([
            'name' => $data['name'],
            'api_key' => $tenantManager->generateApiKey(),
            'db_host' => $data['db_host'],
            'db_port' => $data['db_port'],
            'db_name' => $data['db_name'],
            'db_username' => $data['db_username'],
            'db_password' => $data['db_password'],
            'db_timezone' => $data['db_timezone'] ?? 'UTC',
            'status' => 'active',
        ]);

        $tenantManager->provisionDatabase($shop);

        return response()->json([
            'message' => 'Shop created',
            'shop' => $shop,
        ], 201);
    }
}
