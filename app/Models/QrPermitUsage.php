<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrPermitUsage extends Model
{
    protected $table = 'qr_permit_usages';

    protected $fillable = [
        'application_type',
        'permit_number',
        'permit_number_key',
        'order_number',
        'used_by_uuid',
        'used_at',
        'status',
        'used_lat',
        'used_lng',
        'used_location',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];
}