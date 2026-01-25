<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsignmentLog extends Model
{
    protected $table = 'consignment_logs';

    protected $guarded = ['id'];

    public function causer()
    {
        return $this->morphTo(null, 'causer_type', 'causer_id');
    }

    public function application()
    {
        return $this->belongsTo(ConsignmentApplication::class, 'application_id');
    }
}
