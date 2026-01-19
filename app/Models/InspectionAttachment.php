<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionAttachment extends Model
{
    protected $table = 'inspection_attachments';
    protected $fillable = [
        'item_id',
        'file_name',
        'file_path',
        'file_type',
    ];

    public function item()
    {
        return $this->belongsTo(InspectionItem::class, 'item_id', 'id');
    }
}
