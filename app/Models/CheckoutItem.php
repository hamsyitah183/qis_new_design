<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckoutItem extends Model
{
    protected $table = 'checkout_item';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'ref_checkout',
        'item_id',
        'item_from',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // belongs to a checkout (UUID-based relation)
    public function checkout()
    {
        return $this->belongsTo(Checkout::class, 'ref_checkout', 'uuid');
    }
}
