<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRequirement extends Model
{
    //

    protected $table = 'document_requirements';

    protected $fillable = ['module', 'name', 'description', 'is_required', 'requires_expiry', 'is_active'];
}
