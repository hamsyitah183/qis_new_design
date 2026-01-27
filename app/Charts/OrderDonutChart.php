<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;

class OrderDonutChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\DonutChart
    {
        return $this->chart->donutChart()
        ->setTitle('Order Payment Status')
        ->setSubtitle('2026')
        ->addData([20, 24, 30])
        ->setLabels(['Pending', 'Success', 'Unsuccessful'])
        ->setColors(['#FFC658','#21CE9E','#FB4242'])
        ->setHeight(350)
        ->setFontFamily('inherit');
        // ->setTotalLabel('Total'); // shows TOTAL in center (static)
    
    }
}
