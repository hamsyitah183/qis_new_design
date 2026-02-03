<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoundaryOfficer extends Model
{
    //
    protected $table = 'boundary_officers';

    protected $fillable = [
        'user_id',
        'ip_entry_id',
        'statistic'
    ];

    public function user()
    {
        return $this->belongsTo(InternalUser::class, 'user_id', 'uuid');
    }

    public function entryPoint()
    {
        return $this->belongsTo(IpEntryPoint::class, 'ip_entry_id', 'id');
    }



}
