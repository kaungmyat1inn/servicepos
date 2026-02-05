<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'name',
        'api_key',
        'db_host',
        'db_port',
        'db_name',
        'db_username',
        'db_password',
        'db_timezone',
        'status',
    ];

    protected $hidden = [
        'db_password',
    ];
}
