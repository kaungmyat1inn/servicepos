<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'sort_order',
        'is_closed',
    ];
}
