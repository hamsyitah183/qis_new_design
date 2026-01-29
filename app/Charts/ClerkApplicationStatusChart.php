<?php

namespace App\Charts;

use App\Models\ConsignmentLog;
use App\Models\ImportPermitLog;
use App\Models\InspectionLog;
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
        $pending = ImportPermitLog::where('status', '=', 'Clerk Review In-Progress')->count() +
                    InspectionLog::where('status', '=', 'Clerk review in-progress')->count() +
                   ConsignmentLog::where('status', '=', 'Clerk Review In-Progress')->count();

        $verified = ImportPermitLog::where('status', '=', 'Clerk Verified')->count() +
                    InspectionLog::where('status', '=', 'Clerk Verified')->count() +
                    ConsignmentLog::where('status', '=', 'Clerk Verified')->count();

        $rejected = ImportPermitLog::where('status', '=', 'Clerk Rejected')->count() +
                    InspectionLog::where('status', '=', 'Clerk Rejected')->count() +
                    ConsignmentLog::where('status', '=', 'Clerk Rejected')->count();

        return $this->chart->donutChart()
            ->setTitle('Application Review Status')
            ->setSubtitle('Overview of your current workload')
            ->addData([$pending, $verified, $rejected])
            ->setLabels(['Pending Review', 'Verified', 'Rejected'])
            ->setColors(['#FFB84D', '#2BCD95', '#F14336'])
            ->setFontFamily('inherit');
    }
}
