<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $table = 'orders';
    protected $fillable = ['application_id', 'application_type', 'public_user_uuid', 'order_number', 'status', 'order_details', 'seller_ref', 'fpx_seller_reference', 'name', 'email', 'phone', 'payment_amount', 
    'transaction_data', 'transaction_status', 'kod_transaksi', 'itn', 'sid'];

    protected $casts = [
        'order_details' => 'array',
    ];

    public function publicUser()
    {
        return $this->belongsTo(PublicUser::class, 'public_user_uuid', 'uuid');
    }

    public function ipApplication()
    {
        return $this->belongsTo(
            IpApplication::class,
            'application_id', // orders.application_id
            'application_id', // ip_application.application_id
        )->where('application_type', 'Import Permit');
    }
}
