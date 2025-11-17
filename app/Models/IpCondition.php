<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpCondition extends Model
{
    use HasFactory;
    protected $table = 'ip_condition';

    protected $fillable = [
        'category',
        'item_name',
        'addional_condition',
        'quantity_limit',
        'date_limit',
        'country',
        'usage',
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
}
