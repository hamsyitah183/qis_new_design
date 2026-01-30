<?php

namespace App\Charts;


use Carbon\Carbon;
use App\Models\IpConsignmentPermit;
use App\Models\InspectionItem;
use App\Models\ConsignmentPermit;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class PermitDailyProcessChart
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

            // Count by DATE
            $ipCount = IpConsignmentPermit::whereDate('created_at', $date)->count();

            $inspectionCount = InspectionItem::whereDate('created_at', $date)->count();

            $consignmentCount = ConsignmentPermit::whereDate('created_at', $date)->count();

            $ipData[] = $ipCount;
            $inspectionData[] = $inspectionCount;
            $consignmentData[] = $consignmentCount;
            $totalData[] = $ipCount + $inspectionCount + $consignmentCount;
        }

        return $this->chart->lineChart()
            ->setTitle('Permit Processed')
            ->setSubtitle('Last 7 Days Clerk Workload')
            ->addData($ipData, 'Import Permit')
            ->addData($inspectionData, 'Inspection')
            ->addData($consignmentData, 'Consignment')
            ->addData($totalData, 'Total')
            
            ->setXAxis($days)
            ->setColors([
                '#E354D4', // Import Permit
                '#FF5D9F', // Inspection
                '#9E5CF7', // Consignment
                '#5c67f7', // Total
            ])
            ->setFontFamily('inherit')
            ->setHeight(300);
    }
}
