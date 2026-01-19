<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionItem extends Model
{
    protected $table = 'inspection_items';
    protected $fillable = [
        'application_id',
        'consignment_detail',
        'quantity',
        'unit_measurement',
        'value',
        'purpose',
        'status',
    ];

    protected $casts = [
        'consignment_detail' => 'array',
    ];

    public function application()
    {
        return $this->belongsTo(InspectionApplication::class, 'application_id', 'id');
    }

    public function attachments()
    {
        return $this->hasMany(InspectionAttachment::class, 'item_id', 'id');
    }
}
