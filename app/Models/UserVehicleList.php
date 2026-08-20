<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVehicleList extends Model
{
    //
    protected $fillable = [
        'user_id',
        'vehicle_name',
        'vehicle_number',
        'vehicle_type',
        'vehicle_registration_number',
        'valid_from', 'valid_until'
    ];

}
