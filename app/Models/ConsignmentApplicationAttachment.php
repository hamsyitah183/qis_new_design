<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsignmentApplicationAttachment extends Model
{

    protected $table = 'consignment_application_attachments';

    protected $fillable = [
        'application_id',
        'file_name',
        'file_path',
        'file_type'
    ];

}
