<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicCode extends Model
{
    use HasFactory;

    protected $table = 'public_code';

    protected $fillable = [
        'cate_name',
        'cate_code',
        'description',
        'is_del',
    ];

    protected $casts = [
        'is_del' => 'boolean',
    ];

    public function conversion()
    {
        return $this->hasOne(
            MeasurementUnit::class,
            'measurement_id', // foreign key on measurement_units table
            'id'              // local key on public_code table
        );
    }

    public function consignmentConditions()
    {
        return $this->hasMany(ConsignmentCondition::class, 'category', 'id')
            ->where('cate_name', 'consignment_category');
    }

    // Optional: relationship for condition_category (if needed)
    public function conditionCategories()
    {
        return $this->hasMany(ConsignmentCondition::class, 'category', 'id')
            ->where('cate_name', 'condition_category');
    }
}
