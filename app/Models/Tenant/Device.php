<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'customer_id',
        'brand',
        'model',
        'imei',
        'serial_number',
        'color',
        'issue_description',
    ];

    protected $with = ['customer'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
