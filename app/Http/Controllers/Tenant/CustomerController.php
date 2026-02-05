<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::orderBy('id', 'desc')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => 'required|string|max:120',
            'phone_number' => 'required|string|max:32',
            'email' => 'nullable|email|max:120',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        return Customer::create($data);
    }

    public function show(Customer $customer)
    {
        return $customer;
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'full_name' => 'sometimes|required|string|max:120',
            'phone_number' => 'sometimes|required|string|max:32',
            'email' => 'nullable|email|max:120',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $customer->update($data);

        return $customer;
    }
}
