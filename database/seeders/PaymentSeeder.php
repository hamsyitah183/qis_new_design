<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        $payments = [['name' => 'bayuPay', 'pic' => '/images/payment/bayupay.png'], ['name' => 'yonoPay', 'pic' => '/images/payment/Yonopay.png']];

        foreach ($payments as $payment) {
            PaymentMethod::create([
                'name' => $payment['name'],
                'pic' => $payment['pic'],
            ]);
        }
    }
}
