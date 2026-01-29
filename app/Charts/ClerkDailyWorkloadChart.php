<?php

namespace App\Charts;

use App\Models\ConsignmentLog;
use App\Models\ImportPermitLog;
use App\Models\InspectionLog;
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

        $ipData = [];
        $inspectionData = [];
        $consignmentData = [];
        $totalData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $days[] = $date->format('D, d M');

            $ipCount = ImportPermitLog::whereIn('status', ['Clerk Verified', 'Clerk Rejected'])
                ->whereDate('updated_at', $date)
                ->count();

            $inspectionCount = InspectionLog::whereIn('status', ['Clerk Verified', 'Clerk Rejected'])
                ->whereDate('updated_at', $date)
                ->count();

            $consignmentCount = ConsignmentLog::whereIn('status', ['Clerk Verified', 'Clerk Rejected'])
                ->whereDate('updated_at', $date)
                ->count();

            $ipData[] = $ipCount;
            $inspectionData[] = $inspectionCount;
            $consignmentData[] = $consignmentCount;
            $totalData[] = $ipCount + $inspectionCount + $consignmentCount;
        }

        return $this->chart->lineChart()
        ->setTitle('Applications Processed')
        ->setSubtitle('Last 7 Days Clerk Workload')
        ->addData($ipData, 'Import Permit')
        ->addData($inspectionData, 'Inspection')
        ->addData($consignmentData, 'Consignment')
        ->addData($totalData, 'Total')
        ->setXAxis($days)
        ->setColors([
            '#E354D4', // Import Permit - Blue
            '#FF5D9F', // Inspection - Green
            '#9E5CF7', // Consignment - Yellow
            '#5c67f7', // Total - Red
        ])
        ->setFontFamily('inherit')
        ->setHeight(300);
    
    }
}
