<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $table = 'orders';
    protected $fillable = ['order_number', 'status', 'order_details'];

    protected $casts = [
        'order_details' => 'array', 
    ];
}
