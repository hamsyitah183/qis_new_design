<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    //
    protected $fillable = [
        'user_id',
        'name',
        'path',
        'description',
       
    ];

    public function releasedBy()
    {
        return $this->belongsTo(InternalUser::class, 'released_by', 'uuid');
    }
}
