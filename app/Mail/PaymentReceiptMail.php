<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $paymentData;
    public $permitNumbers;
    public $locale;

    public function __construct($order, $paymentData, $permitNumbers, $locale = null)
    {
        $this->order = $order;
        $this->paymentData = $paymentData;
        $this->permitNumbers = $permitNumbers;
        $this->locale = $locale ?: app()->getLocale();
    }

    public function build()
    {
        $viewName = $this->locale === 'bm' ? 'email.payment_receipt_bm' : 'email.payment_receipt_en';
        $subject = $this->locale === 'bm' ? 'Resit Pembayaran - Pesanan ' . $this->order->order_number : 'Payment Receipt - Order ' . $this->order->order_number;

        return $this->subject($subject)
            ->view($viewName)
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
