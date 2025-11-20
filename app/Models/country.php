<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    // Table name (optional if the model name matches plural 'countries')
    protected $table = 'country';

    // Primary key
    protected $primaryKey = 'id';

    // No timestamps (since your migration has no created_at/updated_at)
    public $timestamps = false;

    // Fillable fields
    protected $fillable = [
        'code',
        'name',
        'is_del',
    ];
}
