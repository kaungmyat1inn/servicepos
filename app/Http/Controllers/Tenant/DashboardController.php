<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Device;
use App\Models\Tenant\Ticket;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary()
    {
        $totalTickets = Ticket::count();
        $openTickets = Ticket::whereNull('closed_at')->count();
        $totalCustomers = Customer::count();
        $totalDevices = Device::count();

        $byStatus = Ticket::select('status_id', DB::raw('count(*) as total'))
            ->groupBy('status_id')
            ->get();

        return [
            'total_tickets' => $totalTickets,
            'open_tickets' => $openTickets,
            'total_customers' => $totalCustomers,
            'total_devices' => $totalDevices,
            'tickets_by_status' => $byStatus,
        ];
    }
}
