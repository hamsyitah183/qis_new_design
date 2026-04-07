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
        'scientific_name',
        'addional_condition',
        'quantity_limit',
        'date_limit',
        'country',
        'usage',
        'start_date',
        'end_date'
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
        return $this->belongsTo(PublicCode::class, 'category', 'cate_code')
                ->where('cate_name', 'condition_category');
    }
}
