<?php

namespace App\Charts;

use App\Models\Order;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Facades\DB;

class PaymentMethodBarChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\BarChart
    {
        $year = now()->year;
    
        // Get monthly sum of payment_amount grouped by payment_type
        $orders = Order::select(
                DB::raw('MONTH(created_at) as month'),
                'payment_type',
                DB::raw('SUM(payment_amount) as total_amount')
            )
            ->whereYear('created_at', $year)
            ->whereNotNull('payment_type')
            ->where('status', 'payment complete') // only successful payments
            ->groupBy('month', 'payment_type')
            ->orderBy('month')
            ->get();
    
        // Group by payment_type
        $grouped = $orders->groupBy('payment_type');
    
        // Initialize chart
        $chart = $this->chart->barChart()
            ->setTitle('Payment Method Revenue by Month')
            ->setHeight(350)
            ->setFontFamily('inherit')
            ->setXAxis(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']);
    
        // Add each series
        foreach ($grouped as $paymentType => $rows) {
            $monthlyData = array_fill(0, 12, 0); // Jan-Dec
    
            foreach ($rows as $row) {
                $monthlyData[$row->month - 1] = (float) $row->total_amount;
            }
    
            // ✅ Correct: first argument = array of numbers, second = series name
            $chart->addData($monthlyData, $paymentType);
        }
    
        // Fallback if no data
        if ($grouped->isEmpty()) {
            $chart->addData(array_fill(0, 12, 0), 'No Data');
        }
    
        return $chart;
    }
    
    
}
