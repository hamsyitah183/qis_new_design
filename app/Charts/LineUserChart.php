<?php

namespace App\Charts;

use App\Models\PublicUser;
use App\Models\InternalUser;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;

class LineUserChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\LineChart
    {
        $publicData = [];
        $internalData = [];
        $months = [];

        // Loop over all 12 months
        for ($i = 1; $i <= 12; $i++) {
            $monthStart = Carbon::createFromDate(null, $i, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            // Public Users registered this month
            $publicCount = PublicUser::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $publicData[] = $publicCount;

            // Internal Users registered this month
            $internalCount = InternalUser::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $internalData[] = $internalCount;

            // Month label
            $months[] = $monthStart->format('F');
        }

        return $this->chart->lineChart()
            ->setTitle('User Registrations in ' . now()->year)
            ->setSubtitle('Public vs Internal Users')
            ->addData($publicData, 'Public User')
            ->addData($internalData, 'Internal User')
            ->setXAxis($months)
            ->setColors([
                '#9E5CF7', // Public User - purple
                '#FF8E6F', // Internal User - orange
            ])
            ->setFontFamily('inherit')
            ->setHeight(300);
    }
}
