<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpConsignmentAttachment extends Model
{
    use HasFactory;
    protected $table = 'ip_consignment_attachment';

    protected $fillable = [
        'permit_id',
        'file_name',
        'file_path',
        'file_type'
    ];

}
