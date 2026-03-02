<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeasurementUnit extends Model
{
    //

    protected $table = 'measurement_units';

    protected $fillable = [
        'measurement_id',
        'conversion',
    ];

    public function publicCode()
    {
        return $this->belongsTo(PublicCode::class, 'measurement_id', 'id');
    }
}
