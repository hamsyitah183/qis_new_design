<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;

class LineUserChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\LineChart
    {
        return $this->chart->lineChart()
            ->setTitle('User Registration')
            ->setSubtitle('Public User and Internal User')
            ->addData([40, 93, 35, 42, 18, 82], 'Public User')
            ->addData([70, 29, 77, 28, 55, 45], 'Internal User')
            ->setXAxis(['January', 'February', 'March', 'April', 'May', 'June'])
            ->setColors([
                '#9E5CF7', // Public User - blue
                '#FF8E6F', // Internal User - green
            ])
            ->setFontFamily('inherit');
    }
    
}
