<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'full_name',
        'phone_number',
        'email',
        'address',
        'notes',
    ];

    protected $with = ['devices'];

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
