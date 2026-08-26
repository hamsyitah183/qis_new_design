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
        'start_date',
        'end_date',
        'measurement_unit',
        'item_bahasa',

        'another_name',
        'attachment'
    ];

    protected $casts = [
        'country' => 'array', // JSON
        'usage'   => 'array', // JSON
        'quantity_limit' => 'float',
        'date_limit'     => 'date',

        'another_name' => 'array',  // <-- added
        'attachment'   => 'array',  // <-- added
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

    public function countries()
    {
        return Country::whereIn('code', $this->country ?? []);
    }
}
