<?php

namespace App\Models;

use App\Traits\HasApplicationActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentApplication extends Model
{
    use HasFactory, HasApplicationActivityLog;
    protected $table = 'consignment_applications';

    protected $fillable = ['application_id', 'reference_no', 'eta', 'transport_type', 'entry_point', 'user_id', 'exporter_id', 'importer_id', 'importer_detail', 'category_application', 'importer_verify', 'date_importer_verify', 'status'];

    protected $casts = [
        'eta' => 'date',
        'importer_detail' => 'array', // JSON stored importer info
        'date_importer_verify' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($application) {
            $application->application_type = 'Consignment Certificate';
        });
    }
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
        return $this->belongsTo(ConsignmentImporter::class, 'importer_id', 'id');
    }

    // Exporter information
    public function exporter()
    {
        return $this->belongsTo(PublicUser::class, 'exporter_id', 'uuid');
    }

    public function entryPoint()
    {
        return $this->belongsTo(IpEntryPoint::class, 'entry_point', 'id');
    }

    public function consignmentPermits()
    {
        return $this->hasMany(ConsignmentPermit::class, 'application_id', 'id');
    }

    public function activity_log()
    {
        return $this->hasMany(ImportPermitLog::class, 'application_id', 'application_id');
    }

    public function latestLog()
    {
        return $this->hasOne(ImportPermitLog::class, 'application_id', 'application_id')
            ->latestOfMany();
    }
}
