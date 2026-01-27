<?php

namespace App\Charts;

use App\Models\IpApplication;
use App\Models\InspectionApplication;
use App\Models\ConsignmentApplication;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;

class ClerkDailyVolumeChart
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
            
            // Total submissions across all modules (regardless of status)
            $count = IpApplication::whereDate('created_at', '=', $date)->count();
            $count += InspectionApplication::whereDate('created_at', '=', $date)->count();
            $count += ConsignmentApplication::whereDate('created_at', '=', $date)->count();
                
            $data[] = $count;
        }

        return $this->chart->lineChart()
            ->setTitle('Daily Application Volume')
            ->setSubtitle('Total submissions across all modules (Last 7 Days)')
            ->addData($data, 'Total Submissions')
            ->setXAxis($days)
            ->setColors(['#F14336']) // Red as per image 2
            ->setFontFamily('inherit')
            ->setHeight(300);
    }
}
