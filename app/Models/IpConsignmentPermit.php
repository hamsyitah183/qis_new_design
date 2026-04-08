<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpConsignmentPermit extends Model
{
    use HasFactory;

    protected $table = 'ip_consignment_permit';

    protected $fillable = ['application_id', 'permit_number', 'consignment_detail', 'quantity', 'unit_measurement', 'value', 'purpose', 'status', 'remark', 'validity_date'];

    protected $casts = [
        'consignment_detail' => 'array', // JSON (id, category, item_name, usage)
        'quantity' => 'float',
        'value' => 'float',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    // Each consignment belongs to an IP Application
    public function application()
    {
        return $this->belongsTo(IpApplication::class, 'application_id', 'id');
    }

    // Unit measurement (from public_code)
    public function unit()
    {
        return $this->belongsTo(PublicCode::class, 'unit_measurement', 'cate_code')->where('cate_name', 'unit_measurement');
    }

    // Purpose (from public_code)
    public function purposeCode()
    {
        return $this->belongsTo(PublicCode::class, 'unit_measurement', 'cate_code')->where('cate_name', 'consignment_purpose');
    }

    public function attachments()
    {
        return $this->hasMany(IpConsignmentAttachment::class, 'permit_id', 'id');
    }

    public function getItemNameAttribute()
    {
        return data_get($this->consignment_detail, 'item_name', '-');
    }

    public function condition()
    {
        $itemId = data_get($this->consignment_detail, 'item_id');

        return $itemId 
            ? IpCondition::find($itemId)
            : null;
    }
}
