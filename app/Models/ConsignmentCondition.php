<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentCondition extends Model
{
    //
    use HasFactory;
    protected $table = 'consignment_conditions';

    protected $fillable = [
        'category',
        'item_name',
        'addional_condition',
        'scientific_name',
        'quantity_limit',
        'date_limit',
        'country',
        'usage',
        'start_date',
        'end_date',
        'item_bahasa',
    ];

    protected $casts = [
        'country' => 'array', // JSON
        'usage'   => 'array', // JSON
        'quantity_limit' => 'float',
        'date_limit'     => 'date',
    ];

    public function code()
    {
        return $this->belongsTo(PublicCode::class, 'category', 'cate_code');
    }

    public function condcategory()
    {
        return $this->belongsTo(PublicCode::class, 'category', 'id')
            ->where('cate_name', 'consignment_category');
    }
}
