<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionApplicationAttachment extends Model
{

    protected $table = 'inspection_application_attachments';

    protected $fillable = [
        'application_id',
        'file_name',
        'file_path',
        'file_type',
        'description'
    ];

}
