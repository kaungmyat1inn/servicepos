<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'ticket_no',
        'customer_id',
        'device_id',
        'status_id',
        'problem_summary',
        'technician_notes',
        'estimated_cost',
        'final_cost',
        'received_at',
        'due_at',
        'closed_at',
    ];

    protected $with = ['customer', 'device', 'status'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }
}
