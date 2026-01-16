<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsignmentAttachment extends Model
{
    use HasFactory;

    protected $table = 'consignment_attachments';

    protected $fillable = [
        'permit_id',
        'file_name',
        'file_path',
        'file_type',
        'description',
        'is_del',
    ];

    protected $casts = [
        'is_del' => 'boolean',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    // Each attachment belongs to a Consignment Permit
    public function permit()
    {
        return $this->belongsTo(ConsignmentPermit::class, 'permit_id', 'id');
    }
}
