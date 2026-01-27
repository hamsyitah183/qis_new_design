<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;

class PaymentMethodBarChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\BarChart
    {
        return $this->chart
            ->barChart()
            ->setTitle('Payment Method')

            ->addData([6, 9, 3, 4, 10, 8], 'BayuPay')
            ->addData([7, 3, 8, 2, 6, 4], 'Cash')
            ->setXAxis(['January', 'February', 'March', 'April', 'May', 'June'])
            ->setHeight(350)
            ->setFontFamily('inherit');
    }
}
