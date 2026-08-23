<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAttachment extends Model
{
    protected $fillable = [
        'user_id',
        'document_type',
        'file_path',
        'file_type',
        'file_size',
        'original_file_name',
        'valid_from',
        'valid_until',
        'is_read',
        'rejected_reason',
    ];
    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(PublicUser::class, 'user_id', 'uuid');
    }
}
