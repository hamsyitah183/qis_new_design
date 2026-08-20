<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementAttachment extends Model
{
    protected $fillable = [
        'announcement_id',
        'file_name',
        'file_path',
        'file_type',
        'uploaded_by',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(InternalUser::class, 'uploaded_by', 'uuid');
    }
}
