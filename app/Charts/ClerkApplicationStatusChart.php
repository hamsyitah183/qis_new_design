<?php

namespace App\Charts;

use App\Models\IpApplication;
use App\Models\InspectionApplication;
use App\Models\ConsignmentApplication;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class ClerkApplicationStatusChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\DonutChart
    {
        $pending = IpApplication::where('status', '=', 'Clerk Review In-Progress')->count() +
                   InspectionApplication::where('status', '=', 'Clerk review in-progress')->count() +
                   ConsignmentApplication::where('status', '=', 'Clerk Review In-Progress')->count();

        $verified = IpApplication::where('status', '=', 'Clerk Verified')->count() +
                    InspectionApplication::where('status', '=', 'Clerk Verified')->count() +
                    ConsignmentApplication::where('status', '=', 'Clerk Verified')->count();

        $rejected = IpApplication::where('status', '=', 'Clerk Rejected')->count() +
                    InspectionApplication::where('status', '=', 'Clerk Rejected')->count() +
                    ConsignmentApplication::where('status', '=', 'Clerk Rejected')->count();

        return $this->chart->donutChart()
            ->setTitle('Application Review Status')
            ->setSubtitle('Overview of your current workload')
            ->addData([$pending, $verified, $rejected])
            ->setLabels(['Pending Review', 'Verified', 'Rejected'])
            ->setColors(['#FFB84D', '#2BCD95', '#F14336'])
            ->setFontFamily('inherit');
    }
}
