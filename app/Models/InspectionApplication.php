<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InspectionLog;
use App\Traits\HasInspectionActivityLog;

class InspectionApplication extends Model
{
    use HasInspectionActivityLog;

    protected $table = 'inspection_applications';
    protected $fillable = [
        'application_id',
        'user_id',
        'importer_id',
        'exporter_id',
        'importer_detail',
        'eta',
        'transport_type',
        'entry_point',
        'category_application',
        'status',
    ];

    protected $casts = [
        'eta' => 'date',
        'importer_detail' => 'array',
    ];


    public function user()
    {
        return $this->belongsTo(PublicUser::class, 'user_id', 'uuid');
    }

    public function importer()
    {
        return $this->belongsTo(PublicUser::class, 'importer_id', 'uuid');
    }

    public function exporter()
    {
        return $this->belongsTo(Exporter::class, 'exporter_id', 'id');
    }

    public function entryPoint()
    {
        return $this->belongsTo(IpEntryPoint::class, 'entry_point', 'id');
    }

    public function inspectionItems()
    {
        return $this->hasMany(InspectionItem::class, 'application_id', 'id');
    }

    public function activity_log()
    {
        return $this->hasMany(InspectionLog::class, 'application_id', 'application_id');
    }

    public function latestLog()
    {
        return $this->hasOne(InspectionLog::class, 'application_id', 'application_id')->latestOfMany();
    }
}
