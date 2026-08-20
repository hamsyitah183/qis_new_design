<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'released_by',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    /**
     * Get the user that released the announcement.
     */
    public function releasedBy()
    {
        return $this->belongsTo(InternalUser::class, 'released_by', 'uuid');
    }
}
