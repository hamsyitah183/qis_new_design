<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrScanLog extends Model
{
    protected $table = 'qr_scan_logs';

    protected $fillable = [
        'internal_user_uuid',
        'internal_user_name',
        'internal_user_position',
        'scanned_value',
        'permit_number',
        'order_number',
        'application_type',
        'is_valid',
        'result',
        'scanned_at',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    public function internalUser()
    {
        return $this->belongsTo(InternalUser::class, 'internal_user_uuid', 'uuid');
    }
}
