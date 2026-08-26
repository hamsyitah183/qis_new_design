<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpUses extends Model
{
    //
    protected $table = 'ip_uses';

    protected $fillable = [
        'name'
    ];

    public function getUsageArrayAttribute()
    {
        return is_string($this->usage) ? json_decode($this->usage, true) : ($this->usage ?? []);
    }
}
