<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionLog extends Model
{
    protected $table = 'inspection_logs';

    protected $fillable = [
        'application_id',
        'causer_id',
        'causer_type',
        'status',
        'action',
        'remark',
    ];

    public function causer()
    {
        return $this->morphTo(null, 'causer_type', 'causer_id');
    }

    public function application()
    {
        return $this->belongsTo(InspectionApplication::class, 'application_id');
    }
}



