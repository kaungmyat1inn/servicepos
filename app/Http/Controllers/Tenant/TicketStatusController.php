<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TicketStatus;
use Illuminate\Http\Request;

class TicketStatusController extends Controller
{
    public function index()
    {
        return TicketStatus::orderBy('sort_order', 'asc')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:64|unique:ticket_statuses,name',
            'sort_order' => 'nullable|integer',
            'is_closed' => 'nullable|boolean',
        ]);

        return TicketStatus::create($data);
    }

    public function show(TicketStatus $status)
    {
        return $status;
    }

    public function update(Request $request, TicketStatus $status)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:64|unique:ticket_statuses,name,' . $status->id,
            'sort_order' => 'nullable|integer',
            'is_closed' => 'nullable|boolean',
        ]);

        $status->update($data);

        return $status;
    }

    public function destroy(TicketStatus $status)
    {
        $status->delete();

        return response()->json(['message' => 'Status deleted']);
    }
}

