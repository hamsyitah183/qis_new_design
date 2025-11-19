<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpApplication extends Model
{
    use HasFactory;
    protected $table = 'ip_application';

    protected $fillable = [
        'application_id',
        'eta',
        'transport_type',
        'entry_point',
        'user_id',
        'exporter_id',
        'importer_id',
        'importer_detail',
        'category_application',
        'importer_verify',
        'date_importer_verify',
    ];

    protected $casts = [
        'eta' => 'date',
        'importer_detail' => 'array',          // JSON stored importer info
        'importer_verify' => 'boolean',
        'date_importer_verify' => 'datetime',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    // Who submitted the application
    public function user()
    {
        return $this->belongsTo(PublicUser::class, 'user_id', 'uuid');
    }

    public function importer()
    {
        return $this->belongsTo(PublicUser::class, 'importer_id', 'uuid');
    }

    // Exporter information
    public function exporter()
    {
        return $this->belongsTo(Exporter::class, 'exporter_id', 'id');
    }

    public function entryPoint()
    {
        return $this->belongsTo(IpEntryPoint::class, 'entry_point', 'id');
    }

    public function consignmentPermits()
    {
        return $this->hasMany(IpConsignmentPermit::class, 'application_id', 'id');
    }
}
