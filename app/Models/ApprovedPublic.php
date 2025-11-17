<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovedPublic extends Model
{
    //

    protected $table = 'approved_publics';

    protected $fillable = [
        'doa_verified',
        'verification_attachment',
        'approved_by', 'status', 'reason'
    ];

    public function publicUser()
    {
        return $this->belongsTo(PublicUser::class, 'user_id', 'uuid');
    }

    public function approver()
    {
        return $this->belongsTo(InternalUser::class, 'approved_by', 'uuid');
    }
}
