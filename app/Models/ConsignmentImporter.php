<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentImporter extends Model
{
    use HasFactory;

    protected $table = 'consignment_importers';

    protected $fillable = ['name', 'phone_no', 'address', 'country', 'registered_by'];

    public function registeredBy()
    {
        return $this->belongsTo(PublicUser::class, 'registered_by');
    }

    public function countryInfo()
    {
        return $this->belongsTo(Country::class, 'country', 'code');
    }
}