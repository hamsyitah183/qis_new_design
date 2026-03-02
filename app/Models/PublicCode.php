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
        
    }    
}
