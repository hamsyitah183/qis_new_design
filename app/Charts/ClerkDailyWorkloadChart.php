<?php

namespace App\Charts;

use App\Models\IpApplication;
use App\Models\InspectionApplication;
use App\Models\ConsignmentApplication;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;

class ClerkDailyWorkloadChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\LineChart
    {
        $days = [];
        $data = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days[] = $date->format('D, d M');
            
            // Count verified or rejected by clerk on this day
            $count = IpApplication::whereIn('status', ['Clerk Verified', 'Clerk Rejected'])
                ->whereDate('updated_at', '=', $date)->count();
            $count += InspectionApplication::whereIn('status', ['Clerk Verified', 'Clerk Rejected'])
                ->whereDate('updated_at', '=', $date)->count();
            $count += ConsignmentApplication::whereIn('status', ['Clerk Verified', 'Clerk Rejected'])
                ->whereDate('updated_at', '=', $date)->count();
                
            $data[] = $count;
        }

        return $this->chart->lineChart()
            ->setTitle('Applications Processed')
            ->setSubtitle('Last 7 days performance')
            ->addData($data, 'Applications')
            ->setXAxis($days)
            ->setColors(['#009EF7'])
            ->setFontFamily('inherit')
            ->setHeight(300);
    }
}
