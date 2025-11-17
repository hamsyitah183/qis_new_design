<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempAttachment extends Model
{
     protected $fillable = [
        'temp_name',
        'original_name',
        'mime_type',
        'size',
        'temp_path'
    ];
}
