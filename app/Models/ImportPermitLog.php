<?php

namespace App\Models;

use App\Traits\HasApplicationActivityLog;
use Illuminate\Database\Eloquent\Model;

class ImportPermitLog extends Model
{
    //

    protected $table = 'ip_logs';

    protected $fillable = [
        'application_id',

        'user_id',
        'status',
        'action',
        'remark',
        'causer_id',
        'causer_type'
    ];


    public function causer()
    {
        return $this->morphTo(null, 'causer_type', 'causer_id');
    }

    public function application()
    {
        return $this->belongsTo(IpApplication::class, 'application_id');
    }
}
