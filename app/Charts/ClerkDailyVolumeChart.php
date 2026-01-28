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

        $totalData = [];
        $ipData = [];
        $inspectionData = [];
        $consignmentData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $days[] = $date->format('D, d M');

            $ipCount = IpApplication::whereDate('created_at', $date)->count();
            $inspectionCount = InspectionApplication::whereDate('created_at', $date)->count();
            $consignmentCount = ConsignmentApplication::whereDate('created_at', $date)->count();

            $ipData[] = $ipCount;
            $inspectionData[] = $inspectionCount;
            $consignmentData[] = $consignmentCount;

            $totalData[] = $ipCount + $inspectionCount + $consignmentCount;
        }

        return $this->chart->lineChart()
        ->setTitle('Daily Application Volume')
        ->setSubtitle('Total submissions across all modules (Last 7 Days)')
        ->addData($totalData, 'Total Submissions')
        ->addData($ipData, 'Import Permit')
        ->addData($inspectionData, 'Inspection')
        ->addData($consignmentData, 'Consignment')
        ->setXAxis($days)
        ->setHeight(300)
        ->setFontFamily('inherit');
    

        
    
    }
}
