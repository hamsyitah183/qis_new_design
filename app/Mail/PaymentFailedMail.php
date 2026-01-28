<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $paymentData;
    public $permitNumbers;

    public function __construct($order, $paymentData, $permitNumbers)
    {
        $this->order = $order;
        $this->paymentData = $paymentData;
        $this->permitNumbers = $permitNumbers;
    }

    public function build()
    {
        return $this->subject('Payment Failed - Order ' . $this->order->order_number)
            ->view('email.payment_failed')
            ->with([
                'orderNumber' => $this->order->order_number,
                'fpxReference' => $this->paymentData['fpx_seller_reference'] ?? $this->paymentData['seller_ref'] ?? '-',
                'amount' => $this->paymentData['payment_amount'] ?? $this->order->order_details['total'] ?? '0.00',
                'applicationType' => $this->order->application_type ?? '-',
                'customerName' => $this->paymentData['name'] ?? '-',
                'email' => $this->paymentData['email'] ?? '-',
                'phone' => $this->paymentData['phone'] ?? '-',
                'permitNumbers' => $this->permitNumbers,
            ]);
    }
}
