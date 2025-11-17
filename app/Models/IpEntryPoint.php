<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpEntryPoint extends Model
{
    use HasFactory;

    // Table name (optional because Laravel expects plural, so we define it)
    protected $table = 'ip_entry_point';

    // Fields allowed for mass assignment
    protected $fillable = [
        'district',
        'entry_name',
        'transport_type',
        'is_del',
    ];

    // Casts
    protected $casts = [
        'is_del' => 'boolean',
    ];

    /**
     * Relationship to public_code (district)
     */
    public function districtCode()
    {
        return $this->belongsTo(PublicCode::class, 'district', 'id');
    }
}
