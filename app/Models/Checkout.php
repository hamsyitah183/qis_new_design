<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    /** @use HasFactory<\Database\Factories\CheckoutFactory> */
    use HasFactory;

    protected $table = 'checkout';

    // Primary key is 'id' (auto increment)
    protected $primaryKey = 'id';

    protected $fillable = [
        'uuid',
        'user_uuid',
        'payment_type',
        'transaction_reference',
        'amount',
        'status',
    ];

    // UUID is not incrementing
    public $incrementing = true;

    protected $keyType = 'int';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // One checkout has many items
    public function items()
    {
        return $this->hasMany(CheckoutItem::class, 'ref_checkout', 'uuid');
        // ref_checkout in checkout_item references checkout.uuid
    }

    // Checkout belongs to a public user
    public function user()
    {
        return $this->belongsTo(PublicUser::class, 'user_uuid', 'uuid');
    }
}
