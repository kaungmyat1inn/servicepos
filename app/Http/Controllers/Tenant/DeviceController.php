<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        return Device::orderBy('id', 'desc')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|integer',
            'brand' => 'required|string|max:120',
            'model' => 'required|string|max:120',
            'imei' => 'nullable|string|max:64',
            'serial_number' => 'nullable|string|max:64',
            'color' => 'nullable|string|max:64',
            'issue_description' => 'nullable|string|max:500',
        ]);

        return Device::create($data);
    }

    public function show(Device $device)
    {
        return $device;
    }

    public function update(Request $request, Device $device)
    {
        $data = $request->validate([
            'customer_id' => 'sometimes|required|integer',
            'brand' => 'sometimes|required|string|max:120',
            'model' => 'sometimes|required|string|max:120',
            'imei' => 'nullable|string|max:64',
            'serial_number' => 'nullable|string|max:64',
            'color' => 'nullable|string|max:64',
            'issue_description' => 'nullable|string|max:500',
        ]);

        $device->update($data);

        return $device;
    }
}
