<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpApplicationAttachment extends Model
{
    //
     protected $table = 'ip_application_attachments';

    protected $fillable = [
        // 'permit_id',
        'application_id',
        'file_name',
        'file_path',
        'file_type',
        'description'
    ];
}
