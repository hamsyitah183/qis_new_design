<?php

namespace App\Charts;

use App\Models\Order;
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
        // Get counts of orders grouped by status
        $orderStatuses = Order::select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status'); // returns collection [status => count]

        $labels = $orderStatuses->keys()->toArray(); // ['Pending', 'Success', ...]
        $data = $orderStatuses->values()->toArray(); // [20, 24, 30, ...]

        // Optional: default colors if you want consistent mapping
        $statusColors = [
            'payment pending' => '#FFC658',   // yellow
            'payment authorization' => '#58d0ff',   // yellow
            'payment complete'             => '#21CE9E',   // green
            'payment failed'      => '#FB4242',   // red
        ];

        // Match the color array to the labels dynamically
        $colors = array_map(fn($label) => $statusColors[strtolower($label)] ?? '#6C757D', $labels);

        return $this->chart->donutChart()
            ->setTitle('Order Payment Status')
            ->setSubtitle('2026')
            ->addData($data)
            ->setLabels($labels)
            ->setColors($colors)
            ->setHeight(350)
            ->setFontFamily('inherit');
    }
}
