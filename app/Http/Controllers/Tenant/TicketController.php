<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index()
    {
        return Ticket::orderBy('id', 'desc')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|integer',
            'device_id' => 'required|integer',
            'status_id' => 'required|integer',
            'problem_summary' => 'required|string|max:500',
            'technician_notes' => 'nullable|string|max:1000',
            'estimated_cost' => 'nullable|numeric',
            'final_cost' => 'nullable|numeric',
            'received_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'closed_at' => 'nullable|date',
        ]);

        $data['ticket_no'] = 'T-' . strtoupper(Str::random(8));

        return Ticket::create($data);
    }

    public function show(Ticket $ticket)
    {
        return $ticket;
    }

    public function update(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'status_id' => 'sometimes|required|integer',
            'problem_summary' => 'sometimes|required|string|max:500',
            'technician_notes' => 'nullable|string|max:1000',
            'estimated_cost' => 'nullable|numeric',
            'final_cost' => 'nullable|numeric',
            'received_at' => 'nullable|date',
            'due_at' => 'nullable|date',
            'closed_at' => 'nullable|date',
        ]);

        $ticket->update($data);

        return $ticket;
    }
}
